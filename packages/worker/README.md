# Kafka Bus Worker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kafka-bus/worker.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/worker)

Kafka polling worker infrastructure for [Kafka Bus](https://github.com/kafka-bus/kafka-bus). A `Worker` only knows which topics to read — it reads raw messages from Kafka and dispatches them to a `KafkaBus\Core\Bus`, which owns all routing and handlers.

## Installation

You can install the package via composer:

```bash
composer require kafka-bus/worker
```

## Offset commits

Workers default to `autoCommit: false`. Automatic offset storage is disabled, and
each message is committed synchronously after its dispatcher returns successfully.
If processing throws, the worker does not commit that message. Messages may be
delivered again after a failure, so handlers should tolerate duplicates.

To opt into automatic commits, set `KafkaBus\Worker\Options(autoCommit: true)`.
Automatic commits can confirm a message before processing finishes.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
