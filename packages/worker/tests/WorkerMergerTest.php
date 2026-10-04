<?php

declare(strict_types=1);

namespace KafkaBus\Worker\Tests;

use KafkaBus\Core\Topics\Topic;
use KafkaBus\Worker\Options;
use KafkaBus\Worker\Worker;
use KafkaBus\Worker\WorkerMerger;
use Testo\Assert;
use Testo\Test;

#[Test]
final class WorkerMergerTest
{
    public function singleWorkerIsReturnedUnchanged(): void
    {
        $worker = new Worker('orders-worker', [new Topic('events.orders.1', 'orders')]);

        $merged = (new WorkerMerger())->merge([$worker]);

        Assert::equals($merged->name, 'orders-worker');
        Assert::true($merged === $worker);
    }

    public function mergesTopicsFromMultipleWorkers(): void
    {
        $merged = (new WorkerMerger())->merge([
            new Worker('orders-worker', [new Topic('events.orders.1', 'orders')]),
            new Worker('products-worker', [new Topic('events.products.1', 'products')]),
        ]);

        $topicNames = array_column($merged->topics, 'name');

        Assert::equals($merged->name, 'grouped-worker');
        Assert::array($topicNames)->hasCount(2);
        Assert::true(\in_array('events.orders.1', $topicNames, true));
        Assert::true(\in_array('events.products.1', $topicNames, true));
    }

    public function dedupesTopicsSharedBetweenWorkers(): void
    {
        $ordersTopic = new Topic('events.orders.1', 'orders');

        $merged = (new WorkerMerger())->merge([
            new Worker('first-worker', [$ordersTopic]),
            new Worker('second-worker', [$ordersTopic, new Topic('events.products.1', 'products')]),
        ]);

        Assert::array($merged->topics)->hasCount(2);
    }

    public function preservesOptionsFromFirstWorker(): void
    {
        $firstOptions = new Options(
            additionalOptions: ['group.id' => 'my-group'],
            autoCommit: false,
            consumerTimeout: 5000,
        );

        $merged = (new WorkerMerger())->merge([
            new Worker('orders-worker', [new Topic('events.orders.1', 'orders')], $firstOptions),
            new Worker('products-worker', [new Topic('events.products.1', 'products')]),
        ]);

        Assert::equals($merged->options->additionalOptions, $firstOptions->additionalOptions);
        Assert::equals($merged->options->autoCommit, $firstOptions->autoCommit);
        Assert::equals($merged->options->consumerTimeout, $firstOptions->consumerTimeout);
    }
}
