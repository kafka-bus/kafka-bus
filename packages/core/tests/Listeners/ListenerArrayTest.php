<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Listeners;

use KafkaBus\Core\Bus;
use KafkaBus\Core\Bus\Listeners\ListenerFactory;
use KafkaBus\Core\Bus\Listeners\Workers\MemoryWorkerRegistry;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Bus\Publishers\PublisherFactory;
use KafkaBus\Core\Bus\ThreadFactory;
use KafkaBus\Core\Bus\ThreadRegistry;
use KafkaBus\Core\Consumers\ConsumerStreamFactory;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Consumers\Router\ConsumerRoutesBuilder;
use KafkaBus\Core\Consumers\Router\RouteInfo;
use KafkaBus\Core\Exceptions\Listeners\ListenerException;
use KafkaBus\Core\Producers\ProducerStreamFactory;
use KafkaBus\Core\Testing\Connections\ConnectionFaker;
use KafkaBus\Core\Testing\Connections\ConnectionRegistryFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

#[Test]
final class ListenerArrayTest
{
    private function makeBus(ConnectionFaker $connectionFaker, MemoryWorkerRegistry $workerRegistry): Bus
    {
        return new Bus(
            new ThreadRegistry(
                new ConnectionRegistryFaker($connectionFaker),
                new ThreadFactory(
                    new ListenerFactory(
                        new ConsumerStreamFactory(new MessageHandlerFactory()),
                        $workerRegistry,
                    ),
                    new PublisherFactory(new ProducerStreamFactory()),
                ),
            ),
            'default',
        );
    }

    public function consumesMessagesFromBothWorkers(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $connectionFaker->addMessage(
            MessageFactory::for()
                ->withTopicKey('orders')
                ->make('order-payload'),
        );
        $connectionFaker->addMessage(
            MessageFactory::for()
                ->withTopicKey('products')
                ->make('product-payload'),
        );

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(new Worker('orders-worker', ConsumerRoutesBuilder::make($topicRegistry)
                ->add(new RouteInfo('orders', new VoidConsumerHandlerFaker()))
                ->build()))
            ->add(new Worker('products-worker', ConsumerRoutesBuilder::make($topicRegistry)
                ->add(new RouteInfo('products', new VoidConsumerHandlerFaker()))
                ->build()));

        $bus = $this->makeBus($connectionFaker, $workerRegistry);

        $bus->listener(['orders-worker', 'products-worker'])
            ->listen();

        Assert::array($connectionFaker->committedMessages)
            ->hasCount(2)
            ->hasKeys('events.orders.1', 'events.products.1');
    }

    public function throwsWhenWorkerNameIsUnknown(): void
    {
        $connectionFaker = new ConnectionFaker(new TopicRegistry());

        $bus = $this->makeBus($connectionFaker, new MemoryWorkerRegistry());

        Expect::exception(ListenerException::class)
            ->withMessageContaining('unknown-worker');

        $bus->listener(['unknown-worker']);
    }

    public function throwsWhenOneWorkerInArrayIsUnknown(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'));

        $connectionFaker = new ConnectionFaker($topicRegistry);

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(new Worker('orders-worker', ConsumerRoutesBuilder::make($topicRegistry)
                ->add(new RouteInfo('orders', new VoidConsumerHandlerFaker()))
                ->build()));

        $bus = $this->makeBus($connectionFaker, $workerRegistry);

        Expect::exception(ListenerException::class)
            ->withMessageContaining('ghost-worker');

        $bus->listener(['orders-worker', 'ghost-worker']);
    }
}
