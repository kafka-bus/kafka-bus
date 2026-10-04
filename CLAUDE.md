# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP 8.2+ monorepo of five packages for integrating Apache Kafka into PHP applications via a consumer/producer pipeline architecture. Requires `ext-rdkafka`.

## Commands

```bash
# Install dependencies (the root package autoloads packages/*/src directly)
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

# Benchmarks (PHPBench, Kafka mocked); see benchmarks/README.md
composer bench
composer bench:docker

```

Tests are discovered by Testo via `testo.php` at the repo root. There is no built-in single-file filter; all test suites run together via `vendor/bin/testo`.

## Releasing

Releases are made via a GitHub Release on `1.x` with a `vX.Y.Z` tag — there is no `composer release`. The tag triggers `.github/workflows/split.yml`, which first runs `.github/scripts/pin-interdependencies.sh` (rewrites `kafka-bus/*` constraints from `*` to `^X.Y` in `packages/*/composer.json`, only inside the split repos) and then pushes each package to its own repository; publishing the release triggers `update-changelog.yml`. Keep `*` between packages in the monorepo itself.

## Packages

The root `kafka-bus/kafka-bus` is itself an installable package that ships all five (Moonshine-style): it autoloads `packages/*/src` and lists them in `replace` as `self.version`, maintained by hand. Consumers can `composer require kafka-bus/kafka-bus` or any single package. The root `require` holds only external deps (union of the packages' requirements); when adding a package or an external dependency to one, mirror it in the root `autoload`/`require`. `.gitattributes` keeps dev-only dirs out of the dist archive.

| Package | Directory | Role |
|---------|-----------|------|
| `kafka-bus/core` | `packages/core/` | Bus orchestration, consumer/producer pipelines, Kafka connections, topic routing |
| `kafka-bus/commiter` | `packages/commiter/` | Consumer offset commit middleware, producer idempotency middleware |
| `kafka-bus/messages` | `packages/messages/` | Message DTOs, typed `Payload`, casters, `DomainMessage` base class |
| `kafka-bus/worker` | `packages/worker/` | Kafka polling loop infrastructure (`Worker`, `WorkerRunner`) — reads raw messages and dispatches them to `Bus` |
| `kafka-bus/metadata` | `packages/metadata/` | Kafka cluster metadata: topics/partitions listing, consumer group offsets inspection and administration |

`commiter`, `messages`, `worker` and `metadata` depend only on `core`. All five are versioned and released in sync by the same tag (see Releasing).

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

## Benchmarks (`benchmarks/`)

Dev-only [PHPBench](https://phpbench.readthedocs.io) suite (not a package, not released) measuring the cost of the packages with Kafka mocked. Real-Kafka benchmarks are intentionally out of scope — they are run on a finished application.

- `PublishBench`, `ConsumeBench`, `WorkerBench`, `MemoryLeakBench` (fails if memory grows after N ops + forced GC; `--group=memory`) — `*Bench.php` classes (`bench*` methods, `#[Revs]`, `#[BeforeMethods('setUp')]`); config in root `phpbench.json` (report `kafka-bus`)
- `Fixtures/` — `BusFactory` (builds Bus with N topic routes), `DrainConnection`/`DrainProducer` (publish side must drain the stream so middleware run; core's `NullProducer` does not), `ArrayConsumer`, `Kafka` message helpers. Consume benches use core's `NullConnection`
- `Dockerfile` + `compose.yml` — reproducible run (1 CPU, 512 MB); results go to `benchmarks/results/` (gitignored)
- Included in PHPStan and PHP-CS-Fixer; see `benchmarks/README.md`

## Code Style Constraints

- All files: `declare(strict_types=1)`
- Namespace roots: `KafkaBus\Core`, `KafkaBus\Commiter`, `KafkaBus\Messages`, `KafkaBus\Worker`, `KafkaBus\Metadata` (dev-only: `KafkaBus\Workbench` in `workbench/`, `KafkaBus\Benchmarks` in `benchmarks/`)
- Native function calls: follow what `composer format` produces — PHP-CS-Fixer (`native_function_invocation`) strips the leading `\` from most of them (`array_map()`, `json_encode()`), and `packages/*/src` is written that way. Don't add `\` by hand; run `composer format` instead
- PHPStan level max — no ignored errors without explicit baseline; missing type info for `ext-rdkafka` goes into `stubs/RdKafka.stub`
- PHP-CS-Fixer enforces global namespace imports (no `use function`)