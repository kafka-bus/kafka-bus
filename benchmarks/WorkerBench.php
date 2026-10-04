<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks;

use KafkaBus\Benchmarks\Fixtures\ArrayConsumer;
use KafkaBus\Benchmarks\Fixtures\Kafka;
use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\NullConnection;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Testing\Messages\VoidConsumerHandlerFaker;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Worker\Consumers\ConsumerStream;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Полный цикл ConsumerStream без брокера, 10 топиков: poll → Bus::dispatch → commit. Время — на 1000 сообщений.
 */
#[BeforeMethods('setUp')]
#[Iterations(10)]
#[Warmup(2)]
#[Groups(['worker'])]
final class WorkerBench
{
    private const MESSAGES = 1000;

    private const TOPICS = 10;

    private ArrayConsumer $consumer;

    private Bus $bus;

    /** @var list<Topic> */
    private array $topics = [];

    public function setUp(): void
    {
        $registry = new TopicRegistry();
        for ($n = 0; $n < self::TOPICS; $n++) {
            $topic = new Topic("production.fact.topic-$n.1", "topic-$n");
            $registry->add($topic);
            $this->topics[] = $topic;
        }

        $builder = ReceiverBuilder::make($registry);
        foreach ($this->topics as $topic) {
            $builder->add(new RouteInfo($topic->key, new VoidConsumerHandlerFaker()));
        }

        $this->bus = new Bus(new NullConnection('bench'), receiver: $builder->build());

        // сообщения чередуются по топикам
        $this->consumer = new ArrayConsumer(array_map(
            fn (int $i) => Kafka::message($this->topics[$i % self::TOPICS]->name, 'payload', $i),
            range(0, self::MESSAGES - 1),
        ));
    }

    #[Revs(100)]
    public function benchConsumerStream1000Messages(): void
    {
        $this->consumer->rewind();

        (new ConsumerStream($this->consumer, $this->bus->dispatch(...)))->listen($this->topics);
    }
}
