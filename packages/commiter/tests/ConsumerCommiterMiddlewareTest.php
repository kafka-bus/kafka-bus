<?php

namespace KafkaBus\Commiter\Tests;

use KafkaBus\Commiter\Middleware\ConsumerCommiterMiddleware;
use KafkaBus\Commiter\Repositories\ArrayRepositorySource;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Exceptions\Consumers\MessageConsumerNotHandledException;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\ConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
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

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->withHeaders(['foo' => 'bar'])
            ->make('test-message');

        $repository = new IdempotencyMessageRepository(new ArrayRepositorySource());

        $busFaker = self::buildBusFaker($topicRegistry, new ConsumerHandlerFaker(), $repository);

        $consumerMessage = new ConsumerMessage($message);

        $busFaker->dispatch($consumerMessage);

        $busFaker->assertDispatchedTimes('products', 1);

        Assert::notNull($repository->attempt($consumerMessage)->commitedAt);
    }

    public function doesNotReadIfMessageAlreadyRead(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->withHeaders(['foo' => 'bar'])
            ->make('test-message');

        $repository = new IdempotencyMessageRepository(new ArrayRepositorySource());

        $consumerMessage = new ConsumerMessage($message);
        $repository->commit($consumerMessage);

        $handler = new class () {
            public function __invoke(string $message): void
            {
                throw new RuntimeException('handler should not run');
            }
        };

        $busFaker = self::buildBusFaker($topicRegistry, $handler, $repository);

        $busFaker->dispatch($consumerMessage);

        $busFaker->assertDispatched('products');

        Assert::notNull($repository->attempt($consumerMessage)->commitedAt);
    }

    public function doesNotReadIfMaxAttemptExceeded(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->withHeaders([IdempotencyMessageRepository::HEADER_NAME => 'over-limit'])
            ->make('test-message');

        $source = new ArrayRepositorySource();
        $source->increment('over-limit-production.fact.products.1');
        $source->increment('over-limit-production.fact.products.1');

        $repository = new IdempotencyMessageRepository($source);

        $handler = new class () {
            public function __invoke(string $message): void
            {
                throw new RuntimeException($message);
            }
        };

        $busFaker = $this->buildBusFaker($topicRegistry, $handler, $repository, maxAttempt: 1);

        $busFaker->dispatch(new ConsumerMessage($message));

        $busFaker->assertDispatched('products');

        Assert::same($source->get('over-limit-production.fact.products.1')?->number, 2);
    }

    public function incrementsFailedAttemptAndRethrowsException(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $message = MessageFactory::for()
            ->withTopicKey('products', $topicRegistry)
            ->withHeaders([IdempotencyMessageRepository::HEADER_NAME => 'failing'])
            ->make('test-message');

        $source = new ArrayRepositorySource();
        $source->increment('failing-production.fact.products.1');

        $repository = new IdempotencyMessageRepository($source);

        $handler = new class () {
            public function __invoke(string $message): void
            {
                throw new RuntimeException($message);
            }
        };

        $busFaker = $this->buildBusFaker($topicRegistry, $handler, $repository);

        try {
            $busFaker->dispatch(new ConsumerMessage($message));

            Assert::fail('RuntimeException was expected');
        }
        catch (MessageConsumerNotHandledException $exception) {
            Assert::same($exception->getPrevious()?->getMessage(), 'test-message');
        }

        $busFaker->assertNotDispatched('products');

        Assert::same($source->get('failing-production.fact.products.1')?->number, 2);
        Assert::null($source->get('failing-production.fact.products.1')?->commitedAt);
    }

    private function buildBusFaker(
        TopicRegistry                $topicRegistry,
        callable                     $handler,
        IdempotencyMessageRepository $repository,
        int                          $maxAttempt = -1,
    ): BusFaker {
        return BusFaker::make(
            $topicRegistry,
            new TestoAssertionDriver(),
            receiver: ReceiverBuilder::make($topicRegistry)
                ->add(new RouteInfo('products', $handler, [new ConsumerCommiterMiddleware($repository, maxAttempt: $maxAttempt)]))
                ->build(),
        );
    }
}
