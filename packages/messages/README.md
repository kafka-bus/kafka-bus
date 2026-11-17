# Kafka Bus Messages

JSON messages, domain events and payload casters for Apache Kafka applications in PHP. Serialize outgoing messages and decode incoming records into application objects.

## Installation

Requires PHP 8.2+ and `ext-rdkafka`. Composer installs Kafka Bus Core and the caster assertion dependency.

```bash
composer require kafka-bus/messages
```

## Quick start

Save this next to `vendor/` and run it with PHP:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use KafkaBus\Messages\JsonMessage;

final class OrderPlaced extends JsonMessage {}

$message = OrderPlaced::from(['order_id' => 42, 'total' => 19.95]);
echo $message->toPayload();
// {"order_id":42,"total":19.95}
```

Register `OrderPlaced::class` on a publisher route and call `$bus->publish($message)`. See the [complete quick start](https://github.com/kafka-bus/kafka-bus#quick-start) for connections, routing and a worker.

## Decode messages

Register this handler in a receiver `RouteInfo`:

```php
use KafkaBus\Core\Receivers\Attributes\MessageFactory;
use KafkaBus\Messages\Factories\JsonMessageFactory;

$handler = #[MessageFactory(new JsonMessageFactory(OrderPlaced::class))]
    static function (OrderPlaced $message): void {
        echo $message['order_id'] . PHP_EOL;
    };
```

The attribute selects the factory; the class type alone does not select `JsonMessageFactory`.

## Cast payload fields

Payloads support array access and property access. Override `definitionCasters()` for selected fields; use `$dates` for nullable date fields:

```php
use KafkaBus\Messages\Data\Casters\IntegerCaster;

final class OrderData extends JsonMessage
{
    protected array $dates = ['created_at'];

    protected function definitionCasters(): array
    {
        return ['order_id' => new IntegerCaster()];
    }
}

$order = OrderData::from([
    'order_id' => '42',
    'created_at' => '2026-01-01T12:00:00.000000+03:00',
]);
// order_id is an integer; created_at is a DateTimeInterface.
```

Other casters handle floats, backed enums, nested payloads, collections and nullable values. Date serialization uses UTC without changing the original date object. Casters normalize configured fields; they do not provide complete schema validation.

## Domain events

Extend `DomainMessage` and implement `getKey()` for the Kafka partition key:

```php
use KafkaBus\Messages\DomainEventEnum;
use KafkaBus\Messages\DomainMessage;

final class OrderChanged extends DomainMessage
{
    public function getKey(): ?string
    {
        return (string) $this['order_id'];
    }
}

$event = OrderChanged::create(
    ['order_id' => 42, 'status' => 'paid'],
    DomainEventEnum::Update,
    ['status'],
);
```

The wire format contains `event`, `attributes` and `dirty`. Decode it with `DomainMessageFactory(OrderChanged::class)`. Event cases are `Create`, `Update` and `Delete`.

## JSON depth

Override `public const JSON_DEPTH = 512` in a message subclass to configure the depth used by that class and its corresponding factory. Encoding and decoding use `JSON_THROW_ON_ERROR`; invalid JSON and encoding failures raise `JsonException`.

[Issues and contributions](https://github.com/kafka-bus/kafka-bus) · [Releases](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](https://github.com/kafka-bus/kafka-bus/blob/HEAD/LICENSE.md).
