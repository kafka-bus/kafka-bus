# Kafka Bus Worker

Consumer polling workers for Apache Kafka and PHP. Select topics, configure a consumer group, and dispatch incoming records to Kafka Bus. Workers manage polling and commits; the bus owns routing and handlers.

## Installation

Requires PHP 8.2+ and `ext-rdkafka`. Composer installs Kafka Bus Core.

```bash
composer require kafka-bus/worker
```

## Quick start

Configure a bus and topic registry first. The [complete quick start](https://github.com/kafka-bus/kafka-bus#quick-start) provides a `bootstrap.php` returning `[$bus, $topics]`. With that file in place, save this as `consumer.php` and run `php consumer.php`:

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
    options: new Options(
        additionalOptions: [
            'group.id' => 'orders-service',
            'auto.offset.reset' => 'earliest',
        ],
        consumerTimeout: 2000,
    ),
));

$runner = (new WorkerRunnerFactory($workers))->create($bus, 'orders-worker');
$runner->run();
```

The loop keeps listening until stopped or an unhandled error occurs. `earliest` applies only when no valid committed offset exists for the consumer group.

## Commits and errors

`Options::autoCommit` defaults to `false`. Automatic offset storage is disabled. Each record is committed synchronously after its dispatcher returns successfully. Handler and commit errors propagate to the caller. A handler skipped by middleware still counts as successful dispatch if no exception is raised.

Partition EOF, transport errors and the supported Kafka timeout codes are ignored by the stream; other consume errors propagate. Handlers should tolerate duplicates. Use a process supervisor to manage failed worker processes.

Set `autoCommit: true` explicitly to use automatic commits, which may confirm records before processing completes.

## Graceful stop

On systems with `ext-pcntl`, register these handlers before calling `run()`:

```php
pcntl_async_signals(true);
pcntl_signal(SIGTERM, static fn () => $runner->forceStop());
pcntl_signal(SIGINT, static fn () => $runner->forceStop());

$runner->run();
```

`forceStop()` requests an exit after the current iteration; it does not interrupt a business handler. Create a new runner for a new run after a forced stop.

## Merge workers

Register multiple workers, then call `create($bus, ['orders-worker', 'payments-worker'])`. This combines their topics into one polling loop using the first worker's options. Use separate runners or processes for different consumer groups or settings.

[Issues and contributions](https://github.com/kafka-bus/kafka-bus) · [Releases](https://github.com/kafka-bus/kafka-bus/releases) · [MIT License](https://github.com/kafka-bus/kafka-bus/blob/HEAD/LICENSE.md).
