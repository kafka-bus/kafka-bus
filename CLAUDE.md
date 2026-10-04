# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP 8.2+ monorepo of five packages for integrating Apache Kafka into PHP applications via a consumer/producer pipeline architecture. Requires `ext-rdkafka`.

## Commands

```bash
# Install dependencies (path-repositories create symlinks between packages)
composer install

# Run all tests
composer test

# Run tests (CI mode)
composer test-ci

# Static analysis (PHPStan level max)
composer analyse

# Format code (PHP-CS-Fixer, PSR-12 + PHP 8.2 rules)
composer format

# Validate monorepo consistency
composer validate:monorepo

# Release a new version (bumps all packages in sync)
composer release <version>
```

Tests are discovered by Testo via `testo.php` at the repo root. There is no built-in single-file filter; all test suites run together via `vendor/bin/testo`.

## Packages

| Package | Directory | Role |
|---------|-----------|------|
| `kafka-bus/core` | `packages/core/` | Bus orchestration, consumer/producer pipelines, Kafka connections, topic routing |
| `kafka-bus/commiter` | `packages/commiter/` | Consumer offset commit middleware, producer idempotency middleware |
| `kafka-bus/messages` | `packages/messages/` | Message DTOs, typed `Payload`, casters, `DomainMessage` base class |
| `kafka-bus/worker` | `packages/worker/` | Kafka polling loop infrastructure (`Worker`, `WorkerRunner`) — reads raw messages and dispatches them to `Bus` |
| `kafka-bus/metadata` | `packages/metadata/` | Kafka cluster metadata: topics/partitions listing, consumer group offsets inspection and administration |

`commiter`, `messages`, `worker` and `metadata` depend only on `core`. All five are versioned and released in sync via `monorepo-builder.php`.

## Architecture

`Bus` is a single-connection facade: publishing and consuming both go through it and both have their own router (symmetric design — no separate "listener" subsystem bypassing `Bus`).

- **Publish**: `Bus::publish()` → `Publisher` → `PublisherRouter` (message class → topic) → `PublisherStream`.
- **Consume**: raw Kafka messages are handed to `Bus::dispatch()` → `Receiver` → `ReceiverRouter` (topic → handler) → `RouteExecutor` (message factory + middleware pipeline) → handler.

Reading from Kafka is not `Bus`'s job — a `Worker` (from `kafka-bus/worker`) only knows which topics to poll; it reads raw messages and calls `$bus->dispatch()`, with zero knowledge of handlers or routing.

### Core Package (`packages/core/src/`)

- **`Bus.php`** — the single entry point; holds one `Publisher` and one `Receiver` for a single `ConnectionInterface`
- **`Bus/Publishers/`** — `Publisher`, `PublisherFactory`, `Router/` (message class → topic)
- **`Bus/Consumers/`** — `Receiver`, `ReceiverFactory` (topic → handler, wraps `ReceiverRouter`)
- **`Connections/`** — `KafkaConnectionConfig` (SASL/SSL/plaintext), `KafkaConsumerFactory`, `KafkaProducerFactory`, `ConnectionRegistry`
- **`Consumers/`** — `Consumer` (wraps rdkafka), `Router/` (`ReceiverRouter`, `ConsumerRoutes`, `RouteExecutor`), `ConsumerStream` (generic poll-and-dispatch loop, connection/topics/dispatcher only — no `Worker` knowledge)
- **`Producers/`** — `Producer` (wraps rdkafka), `PublisherStream`, `PublisherPipelineMiddleware`
- **`Topics/`** — `TopicRegistry` and topic metadata
- **`Pipelines/`** — middleware pattern for message processing
- **`Interfaces/`** — public contracts: Bus, Consumer, Producer, Message
- **`Testing/`** — test fakers and factories for use in other packages

### Worker Package (`packages/worker/src/`)

- `Worker` — name + topics + polling `Options` (no handlers, no middleware, no routing)
- `WorkerRunner` / `WorkerRunnerFactory` — builds a raw consumer loop (via core's `ConsumerStreamFactory`) that dispatches every message straight into a `BusInterface`
- `MemoryWorkerRegistry`, `WorkerMerger` — named workers and merging several workers into one poll loop

### Metadata Package (`packages/metadata/src/`)

- `Metadata` — entry point: `Metadata::fromConnection($connection)` (uses only the connection's `Options`) → `topics()`, `consumerGroup($config)`, `partitions($topics, $config, $ownerName)`
- `Topics/` — `TopicsMetadata` (broker-wide `list()`/`get()`), `TopicMetadata`, `PartitionMetadata`
- `ConsumerGroups/` — `ConsumerGroupMetadata` (committed offsets + watermarks per partition, raw `commit()`), `ConsumerPartition`, `PartitionOffset`
- `Partitions/` — `Partitions` / `PartitionsInterface`: high-level API over a set of `Topic`s — list partitions with offsets and set consumer offsets with bounds validation (`CommitOffset`, `CommitOffsetResult`, `Offset`, `TopicPartition`)
- Depends only on `core` — no knowledge of `Worker` or any specific consumer

### Messages Package (`packages/messages/src/`)

- `DomainMessage` — base class for domain events (implements `ProducerMessageInterface`)
- `Payload` — typed DTO with attribute-driven field definition
- `Data/Casters/` — transform raw data to typed values (DateTime, Collection, Nullable, Float, etc.)
- `Factories/` — `DomainMessageFactory`, `JsonMessageFactory`

### Commiter Package (`packages/commiter/src/`)

- `Middleware/` — `ConsumerCommiterMiddleware`, `PublisherIdempotencyMiddleware`
- `Repositories/` — `ArrayMessageRepository`, `NativeMessageRepository`, `IdempotencyMessageRepository`

## Code Style Constraints

- All files: `declare(strict_types=1)`
- Namespace roots: `KafkaBus\Core`, `KafkaBus\Commiter`, `KafkaBus\Messages`, `KafkaBus\Worker`, `KafkaBus\Metadata`
- All native function calls must be fully qualified: `\json_encode()`, `\array_map()`, etc.
- PHPStan level max — no ignored errors without explicit baseline; missing type info for `ext-rdkafka` goes into `stubs/RdKafka.stub`
- PHP-CS-Fixer enforces global namespace imports (no `use function`)