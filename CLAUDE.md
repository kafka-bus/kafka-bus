# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP 8.2+ monorepo of three packages for integrating Apache Kafka into PHP applications via a consumer/producer pipeline architecture. Requires `ext-rdkafka`.

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

`commiter` and `messages` both depend on `core`. All three are versioned and released in sync via `monorepo-builder.php`.

## Architecture

### Core Package (`packages/core/src/`)

- **`Bus/`** — entry point (`Bus`), `ThreadRegistry`, `ListenerFactory`, `PublisherFactory`, `MessageBatch`
- **`Connections/`** — `KafkaConnectionConfig` (SASL/SSL/plaintext), `KafkaConsumerFactory`, `KafkaProducerFactory`, `ConnectionRegistry`
- **`Consumers/`** — `Consumer` (wraps rdkafka), `ConsumerRouter` (topic→handler), `ConsumerStream`, `MessageHandler`
- **`Producers/`** — `Producer` (wraps rdkafka), `PublisherRouter` (message class→topic), `ProducerStream`, `ProducerPipelineMiddleware`
- **`Topics/`** — `TopicRegistry` and topic metadata
- **`Pipelines/`** — middleware pattern for message processing
- **`Interfaces/`** — public contracts: Bus, Consumer, Producer, Message
- **`Testing/`** — test fakers and factories for use in other packages

### Messages Package (`packages/messages/src/`)

- `DomainMessage` — base class for domain events (implements `ProducerMessageInterface`)
- `Payload` — typed DTO with attribute-driven field definition
- `Data/Casters/` — transform raw data to typed values (DateTime, Collection, Nullable, Float, etc.)
- `Factories/` — `DomainMessageFactory`, `JsonMessageFactory`

### Commiter Package (`packages/commiter/src/`)

- `Middleware/` — `ConsumerCommiterMiddleware`, `ProducerIdempotencyMiddleware`
- `Repositories/` — `ArrayMessageRepository`, `NativeMessageRepository`, `IdempotencyMessageRepository`

## Code Style Constraints

- All files: `declare(strict_types=1)`
- Namespace roots: `KafkaBus\Core`, `KafkaBus\Commiter`, `KafkaBus\Messages`
- All native function calls must be fully qualified: `\json_encode()`, `\array_map()`, etc.
- PHPStan level max — no ignored errors without explicit baseline
- PHP-CS-Fixer enforces global namespace imports (no `use function`)