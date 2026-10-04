<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks;

use Closure;
use KafkaBus\Benchmarks\Fixtures\ArrayConsumer;
use KafkaBus\Benchmarks\Fixtures\BusFactory;
use KafkaBus\Benchmarks\Fixtures\Kafka;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage0;
use KafkaBus\Benchmarks\Fixtures\PassThroughMiddleware;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Messages\Factories\DomainMessageFactory;
use KafkaBus\Worker\Consumers\ConsumerStream;
use KafkaBus\Workbench\ProductMessage;
use LogicException;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Проверка на утечки памяти: операция выполняется много раз, затем принудительный GC, и если занятая память выросла
 * больше допуска — бенч падает с ошибкой (в сообщении байты на операцию). Накопление по 1+ байту на операцию за
 * OPERATIONS повторов уже заметно, поэтому допуск — только шум аллокатора.
 *
 * Не проверяются сценарии с ожидаемым ростом (например, ConsumerCommiterMiddleware с ArrayRepositorySource,
 * который хранит все попытки).
 *
 * Время тут — на OPERATIONS операций, как метрику скорости смотрите ConsumeBench / PublishBench.
 */
#[BeforeMethods('setUp')]
#[Iterations(3)]
#[Revs(1)]
#[Warmup(0)]
#[Groups(['memory'])]
final class MemoryLeakBench
{
    private const OPERATIONS = 100_000;

    private const WARMUP_OPERATIONS = 2_000;

    private const TOPICS = 10;

    /** Допуск роста памяти за весь прогон, байт: шум аллокатора и кэши, но не накопление по операциям. */
    private const TOLERANCE_BYTES = 16_384;

    private const STREAM_MESSAGES = 1_000;

    /** @var list<\KafkaBus\Core\Consumers\Messages\ConsumerMessage> */
    private array $messages = [];

    /** @var list<\KafkaBus\Core\Consumers\Messages\ConsumerMessage> */
    private array $domainMessages = [];

    /** @var MessageBatch<TopicMessage0> */
    private MessageBatch $batch;

    private ProductMessage $domainMessage;

    public function setUp(): void
    {
        $productPayload = json_encode(ProductMessage::factory()->makeArray(), JSON_THROW_ON_ERROR);

        for ($n = 0; $n < self::TOPICS * 100; $n++) {
            $topic = BusFactory::topicName($n % self::TOPICS);
            $this->messages[] = Kafka::message($topic, 'payload', $n);
            $this->domainMessages[] = Kafka::message($topic, $productPayload, $n);
        }

        $this->domainMessage = ProductMessage::create(ProductMessage::factory()->makeArray()['attributes']);
        $this->batch = MessageBatch::fromArray(array_map(
            static fn (int $i) => new TopicMessage0("payload-$i"),
            range(1, 100),
        ));
    }

    public function benchNoLeakDispatchRaw(): void
    {
        $bus = BusFactory::receiver(self::TOPICS);
        $messages = $this->messages;
        $count = \count($messages);

        $this->assertNoLeak(static function (int $i) use ($bus, $messages, $count): void {
            $bus->dispatch($messages[$i % $count]);
        });
    }

    public function benchNoLeakDispatchThreeMiddleware(): void
    {
        $bus = BusFactory::receiver(self::TOPICS, [new PassThroughMiddleware(), new PassThroughMiddleware(), new PassThroughMiddleware()]);
        $messages = $this->messages;
        $count = \count($messages);

        $this->assertNoLeak(static function (int $i) use ($bus, $messages, $count): void {
            $bus->dispatch($messages[$i % $count]);
        });
    }

    public function benchNoLeakDispatchDomainMessage(): void
    {
        $bus = BusFactory::receiver(
            self::TOPICS,
            factory: new DomainMessageFactory(ProductMessage::class),
            handler: static function (ProductMessage $message): void {
                $category = $message->category->name;
                $attributes = $message->attributes;
            },
        );
        $messages = $this->domainMessages;
        $count = \count($messages);

        $this->assertNoLeak(static function (int $i) use ($bus, $messages, $count): void {
            $bus->dispatch($messages[$i % $count]);
        });
    }

    public function benchNoLeakPublishRaw(): void
    {
        $bus = BusFactory::publisher();
        $classes = BusFactory::messageClasses();
        $messages = array_map(static fn (string $class) => new $class(), $classes);

        $this->assertNoLeak(static function (int $i) use ($bus, $messages): void {
            $bus->publish($messages[$i % self::TOPICS]);
        });
    }

    public function benchNoLeakPublishBatch(): void
    {
        $bus = BusFactory::publisher();
        $batch = $this->batch;

        $this->assertNoLeak(static function () use ($bus, $batch): void {
            $bus->publishBatch($batch);
        }, self::OPERATIONS / 100);
    }

    public function benchNoLeakPublishDomainMessage(): void
    {
        $bus = BusFactory::publisher(withProductRoute: true);
        $message = $this->domainMessage;

        $this->assertNoLeak(static function () use ($bus, $message): void {
            $bus->publish($message);
        });
    }

    public function benchNoLeakConsumerStream(): void
    {
        $bus = BusFactory::receiver(self::TOPICS);
        $topics = [];
        $messages = [];
        for ($n = 0; $n < self::TOPICS; $n++) {
            $topics[] = new \KafkaBus\Core\Topics\Topic(BusFactory::topicName($n), "topic-$n");
        }
        for ($i = 0; $i < self::STREAM_MESSAGES; $i++) {
            $messages[] = Kafka::message(BusFactory::topicName($i % self::TOPICS), 'payload', $i);
        }
        $consumer = new ArrayConsumer($messages);

        // операция = один прогон потока из STREAM_MESSAGES сообщений
        $this->assertNoLeak(static function () use ($consumer, $bus, $topics): void {
            $consumer->rewind();
            (new ConsumerStream($consumer, $bus->dispatch(...)))->listen($topics);
        }, intdiv(self::OPERATIONS, self::STREAM_MESSAGES));
    }

    /**
     * @param Closure(int): void $operation
     */
    private function assertNoLeak(Closure $operation, int $operations = self::OPERATIONS): void
    {
        for ($i = 0; $i < self::WARMUP_OPERATIONS; $i++) {
            $operation($i);
        }

        gc_collect_cycles();
        $before = memory_get_usage();

        for ($i = 0; $i < $operations; $i++) {
            $operation($i);
        }

        gc_collect_cycles();
        $growth = memory_get_usage() - $before;

        if ($growth > self::TOLERANCE_BYTES) {
            throw new LogicException(\sprintf(
                'Memory leak: +%d bytes after %d operations (%.2f bytes/op), tolerance %d bytes',
                $growth,
                $operations,
                $growth / $operations,
                self::TOLERANCE_BYTES,
            ));
        }
    }
}
