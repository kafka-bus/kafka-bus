# Kafka Bus Commiter

Message processing history and idempotency middleware for PHP and Apache Kafka. Skip messages already marked as processed and limit processing attempts using a repository supplied by your application.

The Composer package name is `kafka-bus/commiter` (one `t`). This middleware tracks processing; Kafka offset commits are handled by the consumer or worker.

## Installation

Requires PHP 8.2+ and `ext-rdkafka`. Composer installs Kafka Bus Core and PSR logging interfaces.

```bash
composer require kafka-bus/commiter
```

## Quick start: track processing

Use this when configuring the `$topics` registry and receiver from the [complete quick start](https://github.com/kafka-bus/kafka-bus#quick-start). Pass the resulting `$receiver` to the bus constructor:

```php
use KafkaBus\Commiter\Middleware\ConsumerCommiterMiddleware;
use KafkaBus\Commiter\Repositories\ArrayRepositorySource;
use KafkaBus\Commiter\Repositories\IdempotencyMessageRepository;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;

$repository = new IdempotencyMessageRepository(new ArrayRepositorySource());
$middleware = new ConsumerCommiterMiddleware($repository, maxAttempt: 3);

$receiver = ReceiverBuilder::make($topics)
    ->add(new RouteInfo(
        topicKey: 'orders',
        handler: static function (array $message): void {
            echo 'Processing order ' . $message['order_id'] . PHP_EOL;
        },
        middleware: [$middleware],
    ))->build();
```

`ArrayRepositorySource` is an in-memory example. Its history disappears when the process ends. Implement `RepositorySourceInterface` with persistent storage for processing history across restarts. Define your storage scope per application or consumer group if several independent consumers share a backend.

`IdempotencyMessageRepository` uses `x-idempotency-key` plus the topic name when the header exists, falling back to the record's topic/partition/offset identity. `NativeMessageRepository` always uses the record identity.

## Attach an idempotency key

Implement `HasIdempotency` on your outgoing message and register the publisher middleware on its route:

```php
use KafkaBus\Commiter\Interfaces\HasIdempotency;
use KafkaBus\Commiter\Middleware\PublisherIdempotencyMiddleware;
use KafkaBus\Core\Publishers\Routing\Options;
use KafkaBus\Messages\JsonMessage;

final class OrderPlaced extends JsonMessage implements HasIdempotency
{
    public function getIdempotencyKey(): string
    {
        return 'order-placed:' . $this['order_id'];
    }
}

// $builder is a PublisherRoutesBuilder configured with your topic registry.
$builder->add(OrderPlaced::class, 'orders', new Options(
    middleware: [new PublisherIdempotencyMiddleware()],
));
```

This snippet also requires `kafka-bus/messages`. Reuse a key only for the same logical event. The header provides application-level identity; it is separate from Kafka producer `enable.idempotence`.

## Processing semantics

- A successful pipeline marks the message processed in the repository.
- A thrown `Throwable` records a failure and propagates to the caller.
- Already processed messages skip the remaining pipeline.
- `maxAttempt` limits processing attempts; the default `-1` leaves attempts unlimited. The middleware does not schedule retries.
- Exceeding the limit skips the pipeline and returns normally. A worker can then commit that record's offset.

Repository tracking alone does not make business writes atomic or guarantee exactly-once processing. Coordinate durable history with business transactions where required.

[Issues and contributions](https://github.com/kafka-bus/kafka-bus) · [Releases](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](https://github.com/kafka-bus/kafka-bus/blob/HEAD/LICENSE.md).
