<?php

declare(strict_types=1);

namespace KafkaBus\Core\Tests\Listeners;

use KafkaBus\Core\Bus\Listeners\Workers\Options;
use KafkaBus\Core\Bus\Listeners\Workers\Worker;
use KafkaBus\Core\Bus\Listeners\Workers\WorkerMerger;
use KafkaBus\Core\Consumers\Handlers\MessageHandlerFactory;
use KafkaBus\Core\Consumers\Router\ConsumerRoutesBuilder;
use KafkaBus\Core\Consumers\Router\RouteInfo;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use Testo\Assert;
use Testo\Test;

#[Test]
final class WorkerMergerTest
{
    private function makeTopicRegistry(string ...$keys): TopicRegistry
    {
        $registry = new TopicRegistry();

        foreach ($keys as $key) {
            $registry->add(new Topic("events.{$key}.1", $key));
        }

        return $registry;
    }

    private function makeWorker(string $name, TopicRegistry $topicRegistry, string ...$topicKeys): Worker
    {
        $builder = ConsumerRoutesBuilder::make($topicRegistry);

        foreach ($topicKeys as $key) {
            $builder->add(new RouteInfo($key, new VoidConsumerHandlerFaker()));
        }

        return new Worker($name, $builder->build());
    }

    public function mergedWorkerIsNamedGroupedWorker(): void
    {
        $registry = $this->makeTopicRegistry('orders');

        $merged = (new WorkerMerger(new MessageHandlerFactory()))->merge([
            $this->makeWorker('orders-worker', $registry, 'orders'),
        ]);

        Assert::equals($merged->name, 'orders-worker');
    }

    public function mergedWorkerContainsAllRoutesFromSingleWorker(): void
    {
        $registry = $this->makeTopicRegistry('orders', 'products');

        $merged = (new WorkerMerger(new MessageHandlerFactory()))->merge([
            $this->makeWorker('events-worker', $registry, 'orders', 'products'),
        ]);

        $topicNames = array_column($merged->topics(), 'name');

        Assert::array($topicNames)->hasCount(2);
        Assert::true(\in_array('events.orders.1', $topicNames, true));
        Assert::true(\in_array('events.products.1', $topicNames, true));
    }

    public function mergesRoutesFromMultipleWorkers(): void
    {
        $registry = $this->makeTopicRegistry('orders', 'products');

        $merged = (new WorkerMerger(new MessageHandlerFactory()))->merge([
            $this->makeWorker('orders-worker', $registry, 'orders'),
            $this->makeWorker('products-worker', $registry, 'products'),
        ]);

        $topicNames = array_column($merged->topics(), 'name');

        Assert::array($topicNames)->hasCount(2);
        Assert::true(\in_array('events.orders.1', $topicNames, true));
        Assert::true(\in_array('events.products.1', $topicNames, true));
    }

    public function preservesOptionsFromFirstWorker(): void
    {
        $registry = $this->makeTopicRegistry('orders', 'products');

        $firstOptions = new Options(
            additionalOptions: ['group.id' => 'my-group'],
            autoCommit: false,
            consumerTimeout: 5000,
        );

        $merged = (new WorkerMerger(new MessageHandlerFactory()))->merge([
            new Worker('orders-worker', ConsumerRoutesBuilder::make($registry)->add(new RouteInfo('orders', new VoidConsumerHandlerFaker()))->build(), $firstOptions),
            $this->makeWorker('products-worker', $registry, 'products'),
        ]);

        Assert::equals($merged->options->additionalOptions, $firstOptions->additionalOptions);
        Assert::equals($merged->options->autoCommit, $firstOptions->autoCommit);
        Assert::equals($merged->options->consumerTimeout, $firstOptions->consumerTimeout);
    }

    public function eachRouteInMergedWorkerGetsItsOwnHandler(): void
    {
        $registry = $this->makeTopicRegistry('orders', 'products');

        $merged = (new WorkerMerger(new MessageHandlerFactory()))->merge([
            $this->makeWorker('orders-worker', $registry, 'orders'),
            $this->makeWorker('products-worker', $registry, 'products'),
        ]);

        $routes = $merged->routes->all();

        Assert::array($routes)->hasCount(2);
        Assert::equals($routes[0]->topic->name, 'events.orders.1');
        Assert::equals($routes[1]->topic->name, 'events.products.1');
        Assert::false($routes[0]->handler === $routes[1]->handler);
    }
}
