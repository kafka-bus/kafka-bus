<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks;

use KafkaBus\Benchmarks\Fixtures\BusFactory;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage;
use KafkaBus\Benchmarks\Fixtures\Messages\TopicMessage0;
use KafkaBus\Commiter\Middleware\PublisherIdempotencyMiddleware;
use KafkaBus\Core\Bus;
use KafkaBus\Core\Publishers\MessageBatch;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Workbench\ProductMessage;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

/**
 * Публикация без брокера, 10 топиков (по маршруту на каждый TopicMessage0..9): продюсер вычитывает поток сообщений (отрабатывают роутер и middleware), но ничего не отправляет.
 * Время — на один вызов метода; для batch100 — на пачку из 100 сообщений.
 */
#[BeforeMethods('setUp')]
#[Iterations(15)]
#[Warmup(2)]
#[Groups(['publish'])]
final class PublishBench
{
    private const TOPICS = 10;

    private Bus $raw;

    private Bus $idempotency;

    private Bus $domain;

    /** @var list<TopicMessage> */
    private array $messages = [];

    /** @var MessageBatch<TopicMessage0> */
    private MessageBatch $batch;

    private ProductMessage $domainMessage;

    private int $i = 0;

    public function setUp(): void
    {
        $this->raw = BusFactory::publisher();
        $this->idempotency = BusFactory::publisher(new Options(middleware: [new PublisherIdempotencyMiddleware()]));
        $this->domain = BusFactory::publisher(withProductRoute: true);

        foreach (BusFactory::messageClasses() as $messageClass) {
            $this->messages[] = new $messageClass();
        }

        $this->domainMessage = ProductMessage::create(ProductMessage::factory()->makeArray()['attributes']);
        $this->batch = MessageBatch::fromArray(array_map(
            static fn (int $i) => new TopicMessage0("payload-$i"),
            range(1, 100),
        ));
    }

    #[Revs(30000)]
    public function benchPublishRaw(): void
    {
        $this->raw->publish($this->messages[$this->i++ % self::TOPICS]);
    }

    #[Revs(200)]
    public function benchPublishBatch100(): void
    {
        $this->raw->publishBatch($this->batch);
    }

    #[Revs(30000)]
    public function benchPublishIdempotencyMiddleware(): void
    {
        $this->idempotency->publish($this->messages[$this->i++ % self::TOPICS]);
    }

    #[Revs(5000)]
    public function benchPublishDomainMessage(): void
    {
        $this->domain->publish($this->domainMessage);
    }
}
