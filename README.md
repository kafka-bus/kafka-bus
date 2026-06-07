# kafka-bus-core

Монорепозиторий core-пакетов экосистемы [kafka-bus](https://github.com/kafka-bus/kafka-bus).

## Пакеты

| Пакет                | Директория          | Packagist                                                                                                                                           |
|----------------------|---------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| `kafka-bus/core`     | `packages/core`     | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/core.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/core)         |
| `kafka-bus/commiter` | `packages/commiter` | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/commiter.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/commiter) |
| `kafka-bus/messages` | `packages/messages` | [![Latest Version](https://img.shields.io/packagist/v/kafka-bus/messages.svg?style=flat-square)](https://packagist.org/packages/kafka-bus/messages) |

> Laravel- и Spiral-интеграции живут в отдельных репозиториях — у них свой цикл версионирования.

---

## Локальная разработка

```bash
# Установить все зависимости (path-repositories создадут symlinks)
composer install

# Запуск тестов
composer test

# PHPStan
composer analyse

# Code style
composer format        # исправить
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Kirill Popkov](https://github.com/popkovkirill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.