<?php

declare(strict_types=1);

namespace KafkaBus\Benchmarks;

use KafkaBus\Benchmarks\Fixtures\BusFactory;
use KafkaBus\Benchmarks\Fixtures\Kafka;
use KafkaBus\Benchmarks\Fixtures\PassThroughMiddleware;
use KafkaBus\Commiter\Middleware\ConsumerCommiterMiddleware;
use KafkaBus\Commiter\Repositories\ArrayRepositorySource;
use KafkaBus\Commiter\Repositories\NativeMessageRepository;
use KafkaBus\Core\Bus;
use KafkaBus\Core\Consumers\Messages\ConsumerMessage;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageConverter;
use KafkaBus\Core\Consumers\Messages\ConsumerMessageInterface;
use KafkaBus\Messages\Factories\DomainMessageFactory;
use KafkaBus\Workbench\ProductMessage;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use RdKafka\Message;

/**
 * Потребление без брокера, 10 топиков (маршрут на каждый; сообщения чередуются по топикам): сообщения заранее лежат в памяти, замеряется только Bus::dispatch и окружающий его код.
 */
#[BeforeMethods('setUp')]
#[Iterations(15)]
#[Warmup(2)]
#[Groups(['consume'])]
final class ConsumeBench
{
    private const POOL = 1024; // степень двойки: индекс = $i & (POOL - 1)

    private const TOPICS = 10;

    private const MANY_TOPICS = 100;

    private Bus $raw;

    private Bus $routes100;

    private Bus $middleware3;

    private Bus $domain;

    private Bus $commiter;

    private ConsumerMessageConverter $converter;

    private Message $rawMessage;

    /** @var list<ConsumerMessage> */
    private array $messages = [];

    /** @var list<ConsumerMessage> */
    private array $domainMessages = [];

    /** @var list<ConsumerMessage> */
    private array $routeMessages = [];

    private int $i = 0;

    public function setUp(): void
    {
        $this->raw = BusFactory::receiver(self::TOPICS);
        $this->routes100 = BusFactory::receiver(self::MANY_TOPICS);
        $this->middleware3 = BusFactory::receiver(self::TOPICS, [
            new PassThroughMiddleware(),
            new PassThroughMiddleware(),
            new PassThroughMiddleware(),
        ]);
        $this->commiter = BusFactory::receiver(self::TOPICS, [
            new ConsumerCommiterMiddleware(new NativeMessageRepository(new ArrayRepositorySource())),
        ]);
        $this->domain = BusFactory::receiver(self::TOPICS, factory: new DomainMessageFactory(ProductMessage::class), handler: static function (ProductMessage $message): void {
            // обращение к полям материализует касты вложенных Payload
            $category = $message->category->name;
            $attributes = $message->attributes;
        });

        $productPayload = json_encode(ProductMessage::factory()->makeArray(), JSON_THROW_ON_ERROR);
        for ($n = 0; $n < self::POOL; $n++) {
            $this->messages[] = Kafka::message(BusFactory::topicName($n % self::TOPICS), 'payload', $n);
            $this->domainMessages[] = Kafka::message(BusFactory::topicName($n % self::TOPICS), $productPayload, $n);
        }

        for ($n = 0; $n < self::MANY_TOPICS; $n++) {
            $this->routeMessages[] = Kafka::message(BusFactory::topicName($n), 'payload', $n);
        }

        $this->converter = new ConsumerMessageConverter();
        $this->rawMessage = Kafka::rawMessage(BusFactory::topicName(0), 'payload');
    }

    #[Revs(50000)]
    public function benchConvertKafkaMessage(): ConsumerMessageInterface
    {
        return $this->converter->fromKafka($this->rawMessage);
    }

    #[Revs(30000)]
    public function benchDispatchRaw(): void
    {
        $this->raw->dispatch($this->messages[$this->i++ & (self::POOL - 1)]);
    }

    #[Revs(30000)]
    public function benchDispatch100Routes(): void
    {
        $this->routes100->dispatch($this->routeMessages[$this->i++ % 100]);
    }

    #[Revs(30000)]
    public function benchDispatchThreeMiddleware(): void
    {
        $this->middleware3->dispatch($this->messages[$this->i++ & (self::POOL - 1)]);
    }

    #[Revs(5000)]
    public function benchDispatchDomainMessage(): void
    {
        $this->domain->dispatch($this->domainMessages[$this->i++ & (self::POOL - 1)]);
    }

    /**
     * У каждого сообщения уникальный offset, иначе коммитер отсечёт повтор. Растущая память — это
     * хранилище ArrayRepositorySource, а не утечка пакета; стоимость создания сообщения — benchCreateMessage.
     */
    #[Revs(5000)]
    public function benchDispatchCommiterMiddleware(): void
    {
        $this->commiter->dispatch(Kafka::message(BusFactory::topicName($this->i % self::TOPICS), 'payload', $this->i++));
    }

    /**
     * Базовая линия для benchDispatchCommiterMiddleware.
     */
    #[Revs(50000)]
    public function benchCreateMessage(): void
    {
        Kafka::message(BusFactory::topicName($this->i % self::TOPICS), 'payload', $this->i++);
    }
}
