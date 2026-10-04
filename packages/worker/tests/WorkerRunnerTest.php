<?php

declare(strict_types=1);

namespace KafkaBus\Worker\Tests;

use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Assertions\TestoAssertionDriver;
use KafkaBus\Core\Testing\BusFaker;
use KafkaBus\Core\Testing\Consumers\MessageFactory;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Worker\Exceptions\WorkerException;
use KafkaBus\Worker\Registry\MemoryWorkerRegistry;
use KafkaBus\Worker\Worker;
use KafkaBus\Worker\WorkerRunnerFactory;
use Testo\Expect;
use Testo\Test;

#[Test]
final class WorkerRunnerTest
{
    private function makeBusFaker(TopicRegistry $topicRegistry): BusFaker
    {
        $receiver = ReceiverBuilder::make($topicRegistry)
            ->add(new RouteInfo('orders', new VoidConsumerHandlerFaker()))
            ->add(new RouteInfo('products', new VoidConsumerHandlerFaker()))
            ->build();

        return BusFaker::make($topicRegistry, new TestoAssertionDriver(), receiver: $receiver);
    }

    public function consumesMessagesFromBothWorkers(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $busFaker = $this->makeBusFaker($topicRegistry);

        $busFaker->addMessage(
            MessageFactory::for()
                ->withTopicKey('orders')
                ->make('order-payload'),
        );
        $busFaker->addMessage(
            MessageFactory::for()
                ->withTopicKey('products')
                ->make('product-payload'),
        );

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(new Worker('orders-worker', [$topicRegistry->get('orders')]))
            ->add(new Worker('products-worker', [$topicRegistry->get('products')]));

        (new WorkerRunnerFactory($workerRegistry))
            ->create($busFaker, ['orders-worker', 'products-worker'])
            ->run();

        $busFaker->assertCommittedTimes('orders', 1);
        $busFaker->assertCommittedTimes('products', 1);
    }

    public function throwsWhenWorkerNameIsUnknown(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $busFaker = $this->makeBusFaker($topicRegistry);

        Expect::exception(WorkerException::class)
            ->withMessageContaining('unknown-worker');

        (new WorkerRunnerFactory(new MemoryWorkerRegistry()))
            ->create($busFaker, ['unknown-worker']);
    }

    public function throwsWhenOneWorkerInArrayIsUnknown(): void
    {
        $topicRegistry = (new TopicRegistry())
            ->add(new Topic('events.orders.1', 'orders'))
            ->add(new Topic('events.products.1', 'products'));

        $workerRegistry = (new MemoryWorkerRegistry())
            ->add(new Worker('orders-worker', [$topicRegistry->get('orders')]));

        $busFaker = $this->makeBusFaker($topicRegistry);

        Expect::exception(WorkerException::class)
            ->withMessageContaining('ghost-worker');

        (new WorkerRunnerFactory($workerRegistry))
            ->create($busFaker, ['orders-worker', 'ghost-worker']);
    }
}
