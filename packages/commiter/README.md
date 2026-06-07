# Kafka Bus Commiter for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kafka-bus/commiter.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/commiter)

A middleware package for [kafka-bus/core](https://github.com/kafka-bus/core) that provides idempotent Kafka
message processing. It tracks which messages have already been handled, prevents duplicate processing, and allows
limiting the maximum number of read attempts.

## Installation

```bash
composer require kafka-bus/commiter
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
