# Kafka Bus for PHP

Apache Kafka messaging for PHP 8.2+, built on php-rdkafka. Publish messages through class-to-topic routes, dispatch records through middleware pipelines, and run consumers with explicit offset commits. Works independently of a framework.

[![Tests](https://github.com/kafka-bus/kafka-bus/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kafka-bus/kafka-bus/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/kafka-bus/kafka-bus/actions/workflows/phpstan.yml/badge.svg)](https://github.com/kafka-bus/kafka-bus/actions/workflows/phpstan.yml)

## Requirements

PHP 8.2+, Composer, the `rdkafka` PHP extension, and an accessible Apache Kafka broker. Confirm the extension with `php --ri rdkafka`; Composer does not install PHP extensions.

The quick start assumes a broker at `localhost:9092` and an existing topic named `orders.events`. Create the topic with your Kafka administration tools first.

## Quick start

```bash
composer require kafka-bus/kafka-bus
```

Create these files next to your application's `vendor` directory.

### 1. Configure the bus

Save as `bootstrap.php`:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use KafkaBus\Core\Bus;
use KafkaBus\Core\Connections\Config\KafkaConnectionConfig;
use KafkaBus\Core\Connections\Registry\ConnectionRegistry;
use KafkaBus\Core\Publishers\PublisherFactory;
use KafkaBus\Core\Publishers\Routing\PublisherRoutesBuilder;
use KafkaBus\Core\Receivers\Routing\ReceiverBuilder;
use KafkaBus\Core\Receivers\Routing\RouteInfo;
use KafkaBus\Core\Topics\Topic;
use KafkaBus\Core\Topics\TopicRegistry;
use KafkaBus\Messages\JsonMessage;

final class OrderPlaced extends JsonMessage {}

$topics = (new TopicRegistry())
    ->add(new Topic('orders.events', 'orders'));
    
$connections = ConnectionRegistry::default();

$routes = PublisherRoutesBuilder::make($topics)
    ->add(OrderPlaced::class, 'orders')->build();
    
$receiver = ReceiverBuilder::make($topics)
    ->add(new RouteInfo('orders', static function (array $message): void {
        echo 'Received order ' . $message['order_id'] . PHP_EOL;
    }))->build();
    
$bus = new Bus(
    connection: $connections->connection('default'),
    publisherFactory: new PublisherFactory(routes: $routes),
    receiver: $receiver,
);

return [$bus, $topics];
```

`orders` is an application alias; `orders.events` is the Kafka topic name. The handler's `array` argument selects JSON decoding automatically.

### 2. Publish a message

Save as `producer.php`, then run `php producer.php`:

```php
<?php

[$bus] = require __DIR__ . '/bootstrap.php'

$bus->publish(OrderPlaced::from(['order_id' => 42]));
```

### 3. Start a consumer

Save as `consumer.php`, then run `php consumer.php`:

```php
<?php

use KafkaBus\Worker\Options;
use KafkaBus\Worker\Registry\MemoryWorkerRegistry;
use KafkaBus\Worker\Worker;
use KafkaBus\Worker\WorkerRunnerFactory;

[$bus, $topics] = require __DIR__ . '/bootstrap.php';

$workers = (new MemoryWorkerRegistry())->add(new Worker(
    name: 'orders-worker',
    topics: [$topics->get('orders')],
    options: new Options(additionalOptions: [
        'group.id' => 'orders-example',
        'auto.offset.reset' => 'earliest',
    ]),
));

$runner = (new WorkerRunnerFactory($workers))->create($bus, 'orders-worker');
$runner->run();
```

The consumer prints `Received order 42` and keeps listening. `earliest` applies when the group has no valid committed offset. Publish another message to see further output.

Workers commit offsets synchronously after successful dispatch. Automatic commits and automatic offset storage are disabled by default. Handler exceptions stop the run before committing that record. Repeated delivery is possible after failures; handlers should tolerate duplicates.

## Packages

The complete package includes all six modules. Alternatively, install modules directly; Composer installs their dependencies.

| Package | Purpose | Documentation |
| --- | --- | --- |
| `kafka-bus/core` | Connections, routing, middleware, bus and test helpers | [Core](https://github.com/kafka-bus/core#readme) |
| `kafka-bus/messages` | JSON messages, domain events, payloads and casters | [Messages](https://github.com/kafka-bus/messages#readme) |
| `kafka-bus/worker` | Polling loops and named consumer workers | [Worker](https://github.com/kafka-bus/worker#readme) |
| `kafka-bus/commiter` | Processing history and idempotency middleware | [Commiter](https://github.com/kafka-bus/commiter#readme) |
| `kafka-bus/metadata` | Topics, partitions and consumer group offsets | [Metadata](https://github.com/kafka-bus/metadata#readme) |
| `kafka-bus/typed-topics` | Multiple message types on one Kafka topic | [Typed topics](https://github.com/kafka-bus/typed-topics#readme) |

For example, this also installs everything needed by the quick start:

```bash
composer require kafka-bus/core kafka-bus/messages kafka-bus/worker
```

## Architecture

Publishing: `Bus::publish()` → class route → publisher middleware → Kafka producer.

Consuming: worker → Kafka record → `Bus::dispatch()` → topic route → message factory → receiver middleware → handler → offset commit.

The bus owns routing; workers select topics and poll Kafka. Laravel and Spiral integrations live in separate repositories with their own release cycles.

## Development

Contribute to this monorepository. The repositories for individual packages are generated from `packages/` on release tags.

```bash
composer install
composer test
composer analyse
composer validate:monorepo
composer format
```

Tests require `ext-rdkafka`, including fixtures that create native records without a broker. See [benchmarks](benchmarks/README.md) for performance tooling.

[Issues](https://github.com/kafka-bus/kafka-bus/issues) · [Release notes](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](LICENSE.md).
