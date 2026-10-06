# kafka-bus-core

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/kafka-bus/kafka-bus/run-tests.yml?branch=1.x&label=tests&style=flat-square)](https://github.com/kafka-bus/kafka-bus/actions?query=workflow%3Arun-tests+branch%3A1.x)
[![GitHub Code Style](https://img.shields.io/github/actions/workflow/status/kafka-bus/kafka-bus/php-code-style.yml?branch=1.x&label=code-style&style=flat-square)](https://github.com/kafka-bus/kafka-bus/actions?query=workflow%3Acode-style+branch%3A1.x)
[![GitHub PHPStan](https://img.shields.io/github/actions/workflow/status/kafka-bus/kafka-bus/phpstan.yml?branch=1.x&label=phpstan&style=flat-square)](https://github.com/kafka-bus/kafka-bus/actions?query=workflow%3Aphpstan+branch%3A1.x)

Монорепозиторий core-пакетов экосистемы [kafka-bus](https://github.com/kafka-bus/kafka-bus).

## Пакеты

| Пакет                | Директория          | Packagist                                                                                                                                           |
|----------------------|---------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| `kafka-bus/core`     | `packages/core`     | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/core.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/core)         |
| `kafka-bus/commiter` | `packages/commiter` | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/commiter.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/commiter) |
| `kafka-bus/messages` | `packages/messages` | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/messages.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/messages) |
| `kafka-bus/worker`   | `packages/worker`   | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/worker.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/worker)     |
| `kafka-bus/metadata` | `packages/metadata` | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/metadata.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/metadata) |

> Laravel- и Spiral-интеграции живут в отдельных репозиториях — у них свой цикл версионирования.

---

## Архитектура

`Bus` — единая точка входа: и публикация, и приём сообщений проходят через него, у каждого направления свой роутер. Чтение из Kafka — не задача `Bus`: `Worker` только поллит топики и передаёт сырые сообщения в `Bus::dispatch()`.

```mermaid
flowchart LR
    M["Message"]
    K1[("Apache Kafka")]
    W["Worker<br/>(poll)"]
    H["Handler"]
    K2[("Apache Kafka")]

    subgraph BUS["Kafka Bus"]
        direction LR
        subgraph PUB["publish"]
            direction LR
            P["Bus::publish()"] --> PR["PublisherRouter<br/>класс сообщения → топик"]
        end
        subgraph CON["dispatch"]
            direction LR
            D["Bus::dispatch()"] --> RR["ReceiverRouter<br/>топик → handler"]
        end
    end

    M -->|"1. produce"| P
    PR --> K1
    K2 --> W
    W -->|"2. consume"| D
    RR --> H
```

Это два независимых процесса (обычно — разные процессы ОС), но оба проходят через один и тот же `Bus`. Apache Kafka на схеме показан дважды только для читаемости — это один и тот же кластер.

---

## Локальная разработка

```bash
# Установить все зависимости (корневой пакет подключает packages/*/src автозагрузкой)
composer install

# Запуск тестов
composer test

# PHPStan
composer analyse

# Code style
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.