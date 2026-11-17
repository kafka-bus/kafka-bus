# Changelog

All notable changes to `kafka-bus` will be documented in this file.

## v2.3.2 - 2026-11-17

### What's Changed

* feat: Добавление JSON-сериализации ConsumerMessage by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/26

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.3.1...v2.3.2

## v2.3.1 - 2026-11-17

### What's Changed

* docs: Обновление документации и метаданных пакетов by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/25

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.3.0...v2.3.1

## v2.3.0 - 2026-11-16

### What's Changed

* fix: Убрать ID сообщения из ошибки необработанного сообщения by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/16
* fix: Обработка ошибок коммита сообщений by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/17
* fix: Очистка пустых настроек соединения by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/18
* fix: Отключить автоматическое сохранение offset по умолчанию by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/19
* fix: Обработка ошибок JSON в сообщениях by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/20
* feat: Поддержка всех форм callable при извлечении фабрики сообщений by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/21
* fix: Использование фабрики сообщений из обработчика маршрута by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/22
* fix: Исправлена ошибка передачи неизвестного типа сообщения в исключение by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/23
* fix: Устранение мутации DateTime при сериализации by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/24

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.2.2...v2.3.0

## v2.2.2 - 2026-11-13

### What's Changed

* feat: Теперь в тестовых фабриках сообщений можно предзаполнять заголовки by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/15

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.2.1...v2.2.2

## v2.2.1 - 2026-11-13

### What's Changed

* fix: Handler теперь стал публичным у HandlerExecutor by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/14

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.2.0...v2.2.1

## v2.2.0 - 2026-11-13

### What's Changed

* feat: Новая фича с типизированными топиками, когда в один топик могут приходить разные сообщения by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/13

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.1.2...v2.2.0

## v2.1.2 - 2026-11-09

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.1.1...v2.1.2

## v2.1.1 - 2026-10-25

### What's Changed

* fix: Исправлена ошибка, когда отключен автокоммит сообщения by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/11

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.1.0...v2.1.1

## v2.1.0 - 2026-10-06

### What's Changed

* fix: Переработана подставка нужного топика при использовании BusFaker by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/10

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.0.1...v2.1.0

## v2.0.1 - 2026-10-04

### What's Changed

* fix: Исправление require у пакетов by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/9

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v2.0.0...v2.0.1

## v2.0.0 - 2026-10-04

### What's Changed

* refactor: Пересмотр функции чтения сообщений через Bus, ранее сообщения читались в обход Kafka Bus by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/8

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.3.0...v2.0.0

## v1.3.0 - 2026-08-12

### What's Changed

* fix: Небольшие исправления by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/6
* feat: Теперь можно указывать dates без definitionCasters by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/7

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.2.1...v1.3.0

## v1.2.1 - 2026-07-23

### What's Changed

* feature: Добавлен EnumCaster by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/5

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.2.0...v1.2.1

## v1.2.0 - 2026-06-16

### What's Changed

* refactor: Доработка Pipeline системы by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/4

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.1.1...v1.2.0

## v1.1.1 - 2026-06-14

### What's Changed

* fix: Не объединять воркеры если был передан только один by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/3

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.1.0...v1.1.1

## v1.1.0 - 2026-06-14

### What's Changed

* feat: Фича по объеднинению воркеров в один Listener by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/2

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.0.1...v1.1.0

## v1.0.1 - 2026-06-08

### What's Changed

* fix: Небольшие исправления by @popkovkirill in https://github.com/kafka-bus/kafka-bus/pull/1

### New Contributors

* @popkovkirill made their first contribution in https://github.com/kafka-bus/kafka-bus/pull/1

**Full Changelog**: https://github.com/kafka-bus/kafka-bus/compare/v1.0.0...v1.0.1
