# Kafka Bus Core

Framework-independent Apache Kafka messaging for PHP: connections, producer and consumer adapters, message routing, middleware pipelines and testing helpers built on php-rdkafka.

## Installation

Requires PHP 8.2+ and `ext-rdkafka`.

```bash
composer require kafka-bus/core
```

## Quick start

With a broker at `localhost:9092` and an existing `orders.events` topic, save this as `publish.php` next to `vendor/`, then run `php publish.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\Config\KafkaConnectionConfig;
use KafkaBus\Core\Connections\Registry\ConnectionRegistry;
use KafkaBus\Core\Producers\Messages\ProducerMessageInterface;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;

final readonly class OrderPlaced implements ProducerMessageInterface
{
    public function __construct(private int $orderId) {}

    public function toPayload(): string
    {
        return json_encode(['order_id' => $this->orderId], JSON_THROW_ON_ERROR);
    }
}

$topics = (new TopicRegistry())->add(new Topic('orders.events', 'orders'));
$connections = ConnectionRegistry::default();

$routes = PublisherRoutesBuilder::make($topics)
    ->add(OrderPlaced::class, 'orders')->build();
    
$bus = new Bus(
    connection: $connections->connection('default'),
    publisherFactory: new PublisherFactory(routes: $routes),
);

$bus->publish(new OrderPlaced(42));
```

## Receive messages

Use the topic registry above to build a receiver and pass it as the `receiver` argument when constructing the bus:

```php
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;

$receiver = ReceiverBuilder::make($topics)
    ->add(new RouteInfo('orders', static function (array $message, int $offset): void {
        echo $message['order_id'] . ' at offset ' . $offset . PHP_EOL;
    }))->build();
```

`Bus::dispatch($record)` accepts `ConsumerMessageInterface`. Dispatch does not poll Kafka or commit offsets. Install [kafka-bus/worker](https://github.com/kafka-bus/worker#readme) for polling, or follow the [complete quick start](https://github.com/kafka-bus/kafka-bus#quick-start).

The first handler argument selects an inferred factory: `string` receives the payload, `array` receives decoded JSON, and `RdKafka\Message` receives the native record. Without an inferred factory, the consumer record wrapper is passed through. Use `#[MessageFactory(...)]` or `ProvidesMessageFactory` to select another factory.

Later arguments can receive `$headers`, `$offset`, `$partition`, `$key`, `$topic`, `$topicName` or `$payload`. Types `ConsumerMessageInterface` and `RdKafka\Message` inject record metadata. Unknown optional arguments retain their defaults.

## Middleware and batches

Register publisher middleware with `KafkaBus\Core\Publishers\Routing\Options(middleware: [...])` in a publisher route. Register receiver middleware with `RouteInfo(middleware: [...])`. Middleware calls `$pipeline->continue()` to run the next stage; returning without continuing skips subsequent stages.

Publish a non-empty batch of the same routed message class:

```php
use KafkaBus\Core\Publishers\MessageBatch;

$bus->publishBatch(MessageBatch::fromArray([
    new OrderPlaced(42),
    new OrderPlaced(43),
]));
```

## Offset configuration

`ConsumerConfig` defaults to `autoCommit: false`, disabling `enable.auto.commit` and `enable.auto.offset.store`. Call `ConsumerInterface::commit($record)` after successful processing. The default committer commits synchronously and retries failures.

Explicit `autoCommit: true` enables automatic commits. Automatic offset storage then follows Kafka defaults or your additional options; offsets may be confirmed before processing finishes.

## Testing

`KafkaBus\Core\Testing\BusFaker` provides publishing, dispatch and commit assertions. Select `PHPUnitAssertionDriver` or `TestoAssertionDriver` for your test framework. Native record fixtures require `ext-rdkafka`.

[Issues and contributions](https://github.com/kafka-bus/kafka-bus) · [Releases](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](https://github.com/kafka-bus/kafka-bus/blob/HEAD/LICENSE.md).
