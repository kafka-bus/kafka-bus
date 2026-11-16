# Kafka Bus for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kafka-bus/core.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/core)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require kafka-bus/core
```

## Consumer offsets

`ConsumerConfig` defaults to `autoCommit: false`. In this mode, `enable.auto.commit`
and `enable.auto.offset.store` are disabled, including when additional options request
automatic offset storage. Call `ConsumerInterface::commit($message)` after successful
processing; it commits synchronously and retries failures.

Set `autoCommit: true` explicitly to use automatic commits. In that mode, automatic
offset storage follows Kafka defaults or your additional options, and offsets may
be committed before processing finishes.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
