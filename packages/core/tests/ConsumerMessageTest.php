<?php

namespace KafkaBus\Core\Tests;

use KafkaBus\Core\Bus;
use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Listeners\Workers\MemoryWorkerRegistry;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Bus\ThreadFactory;
use KafkaBus\Core\Bus\ThreadRegistry;
use KafkaBus\Core\Consumers\ConsumerStreamFactory;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Consumers\Router\ConsumerRoutes;
use KafkaBus\Core\Consumers\Router\Route;
use KafkaBus\Core\Producers\ProducerStreamFactory;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Connections\ConnectionRegistryFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ConsumerMessageTest
{
    public function canConsumeMessage(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('production.fact.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $message = MessageFactory::for()
            ->withHeaders(['foo' => 'bar'])
            ->withTopicKey('products')
            ->make('test-message');

        $connectionFaker->addMessage($message);

        $consumerRoutes = (new ConsumerRoutes())
            ->add(new Route(
                topic: $topicRegistry->get('products'),
                handler: new VoidConsumerHandlerFaker(),
            ));

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(new Worker('default-listener', $consumerRoutes));

        $bus = new Bus(
            new ThreadRegistry(
                new ConnectionRegistryFaker($connectionFaker),
                new ThreadFactory(
                    new ListenerFactory(
                        new ConsumerStreamFactory(new MessageHandlerFactory()),
                        $workerRegistry
                    ),
                    new PublisherFactory(
                        new ProducerStreamFactory(),
                    )
                )
            ),
            'default'
        );

        $bus->listener('default-listener')
            ->listen();

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(1)
            ->hasKeys('production.fact.products.1');

        $message = $connectionFaker->committedMessages['production.fact.products.1'][0]->original();

        Assert::equals($message->payload, 'test-message');
        Assert::equals($message->headers, ['foo' => 'bar']);
    }
}
