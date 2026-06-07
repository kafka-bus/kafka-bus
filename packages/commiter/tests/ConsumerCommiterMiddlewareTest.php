<?php

namespace KafkaBus\Commiter\Tests;

use KafkaBus\Core\Bus;
use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Listeners\Workers\MemoryWorkerRegistry;
use KafkaBus\Core\Bus\Listeners\Workers\Options;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Bus\ThreadFactory;
use KafkaBus\Core\Bus\ThreadRegistry;
use KafkaBus\Core\Connections\Registry\ConnectionRegistry;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Router\ConsumerRoutesBuilder;
use KafkaBus\Core\Consumers\Router\RouteInfo;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Connections\ConnectionRegistryFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\ConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Commiter\Middleware\ConsumerCommiterMiddleware;
use KafkaBus\Commiter\Repositories\ArrayRepositorySource;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use RuntimeException;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ConsumerCommiterMiddlewareTest
{
    public function canConsumeMessage(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $message = MessageFactory::for()
            ->withTopicKey('products')
            ->withHeaders(['foo' => 'bar'])
            ->make('test-message');

        $connectionFaker->addMessage($message);

        $repository = new IdempotencyMessageRepository(new ArrayRepositorySource());

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(
                new Worker(
                    name: 'default-listener',
                    routes: ConsumerRoutesBuilder::make($topicRegistry)
                        ->add(new RouteInfo('products', new ConsumerHandlerFaker()))
                        ->build(),
                    options: new Options(
                        middleware: [new ConsumerCommiterMiddleware($repository)]
                    )
                )
            );

        self::buildBus($connectionFaker, $workerRegistry)
            ->listener('default-listener')
            ->listen();

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(1);
    }

    public function doesNotReadIfMessageAlreadyRead(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $message = MessageFactory::for()
            ->withTopicKey('products')
            ->withHeaders(['foo' => 'bar'])
            ->make('test-message');

        $connectionFaker->addMessage($message);

        $repository = new IdempotencyMessageRepository(new ArrayRepositorySource());
        $repository->commit(new ConsumerMessage($message));

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(
                new Worker(
                    name: 'default-listener',
                    routes: ConsumerRoutesBuilder::make($topicRegistry)
                        ->add(new RouteInfo('products', new ConsumerHandlerFaker()))
                        ->build(),
                    options: new Options(
                        middleware: [new ConsumerCommiterMiddleware($repository)]
                    )
                )
            );

        self::buildBus($connectionFaker, $workerRegistry)
            ->listener('default-listener')
            ->listen();

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(1);
    }

    public function doesNotReadIfMaxAttemptExceeded(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $message = MessageFactory::for()
            ->withTopicKey('products')
            ->withHeaders([IdempotencyMessageRepository::HEADER_NAME => 'over-limit'])
            ->make('test-message');

        $connectionFaker->addMessage($message);

        $source = new ArrayRepositorySource();
        $source->increment('over-limit-production.fact.products.1');
        $source->increment('over-limit-production.fact.products.1');

        $repository = new IdempotencyMessageRepository($source);

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(
                new Worker(
                    name: 'default-listener',
                    routes: ConsumerRoutesBuilder::make($topicRegistry)
                        ->add(new RouteInfo('products', new class () {
                            public function __invoke(string $message): void
                            {
                                throw new RuntimeException($message);
                            }
                        }))
                        ->build(),
                    options: new Options(
                        middleware: [new ConsumerCommiterMiddleware($repository, maxAttempt: 1)]
                    )
                )
            );

        self::buildBus($connectionFaker, $workerRegistry)
            ->listener('default-listener')
            ->listen();

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(1);
    }

    public function incrementsFailedAttemptAndRethrowsException(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $message = MessageFactory::for()
            ->withTopicKey('products')
            ->withHeaders([IdempotencyMessageRepository::HEADER_NAME => 'failing'])
            ->make('test-message');

        $connectionFaker->addMessage($message);

        $source = new ArrayRepositorySource();
        $source->increment('failing-production.fact.products.1');

        $repository = new IdempotencyMessageRepository($source);

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(
                new Worker(
                    name: 'default-listener',
                    routes: ConsumerRoutesBuilder::make($topicRegistry)
                        ->add(new RouteInfo('products', new class () {
                            public function __invoke(string $message): void
                            {
                                throw new RuntimeException($message);
                            }
                        }))
                        ->build(),
                    options: new Options(
                        middleware: [new ConsumerCommiterMiddleware($repository)]
                    )
                )
            );

        try {
            self::buildBus($connectionFaker, $workerRegistry)
                ->listener('default-listener')
                ->listen();

            Assert::fail('RuntimeException was expected');
        }
        catch (MessageConsumerNotHandledException $exception) {
            Assert::same($exception->getPrevious()?->getMessage(), 'test-message');
        }

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(0);

        Assert::same($source->get('failing-production.fact.products.1')?->number, 2);
        Assert::null($source->get('failing-production.fact.products.1')?->commitedAt);
    }

    private static function buildBus(
        ConnectionFaker      $connectionFaker,
        MemoryWorkerRegistry $workerRegistry,
    ): Bus {
        return new Bus(
            new ThreadRegistry(
                new ConnectionRegistryFaker($connectionFaker),
                new ThreadFactory(
                    new ListenerFactory(workerRegistry: $workerRegistry),
                    new PublisherFactory(),
                )
            ),
            ConnectionRegistry::DEFAULT_CONNECTION_NAME
        );
    }
}
