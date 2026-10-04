# Бенчмарки

Бенчмарки на [PHPBench](https://phpbench.readthedocs.io): стоимость самого пакета без брокера и сети.
Kafka замокана — `NullConnection` из core для consume и `DrainConnection` для publish
(продюсер вычитывает поток, чтобы отработали роутер и middleware).
Во всех бенчах 10 топиков (по маршруту на каждый, сообщения чередуются по топикам); `benchDispatch100Routes` — 100 топиков.
Бенчи с реальным Kafka здесь не делаем — их проводят на готовом приложении.

## Запуск

```bash
composer bench                                          # локально (Xdebug в процессах бенчей отключается)
composer bench:docker                                   # в Docker: PHP 8.2 + ext-rdkafka, opcache, 1 CPU, 512 MB
vendor/bin/phpbench run --report=kafka-bus --group=consume
vendor/bin/phpbench run --report=kafka-bus --filter=benchDispatchRaw
```

Конфигурация — `phpbench.json` в корне, отчёт `kafka-bus` (расширяет `aggregate`).
Результат Docker-запуска сохраняется в `benchmarks/results/latest.xml` (каталог в `.gitignore`).

## Сравнение «до / после»

```bash
vendor/bin/phpbench run --report=kafka-bus --tag=before   # на базовой ревизии
vendor/bin/phpbench run --report=kafka-bus --ref=before   # после изменений — покажет разницу
```

Сравнивать числа можно только в одном окружении (одна машина, одна версия PHP); лучше в Docker.

## Что измеряется

Время — на один вызов бенч-метода (`mode`/`mean`/`best`/`worst`, разброс — `rstdev`; > 5–10% — результат шумный),
память — пик процесса (`mem_peak`). Отдельной метрики CPU у PHPBench нет: для однопоточного CPU-bound кода
CPU-время ≈ wall-time.

| Класс          | Бенчи                                                                                              |
|----------------|----------------------------------------------------------------------------------------------------|
| `PublishBench` | `Bus::publish` (сырое, идемпотентность, `DomainMessage`), `publishBatch` — время на пачку из 100   |
| `ConsumeBench` | конвертация сообщения, `Bus::dispatch` (1 и 100 маршрутов, 3 middleware, `DomainMessageFactory`, `ConsumerCommiterMiddleware`) |
| `MemoryLeakBench` | проверка утечек: 100 000 операций → принудительный GC → падает, если память выросла больше чем на 16 КБ |
| `WorkerBench`  | полный цикл `ConsumerStream`: poll → dispatch → commit, время на 1000 сообщений                    |

Утечки: `vendor/bin/phpbench run --report=kafka-bus --group=memory`. Успешный прогон выглядит как обычный отчёт,
при утечке бенч помечается ошибкой с числом байт на операцию. Колонка `mem_peak` для этого не годится — в ней
в основном память кода и фикстур. `ConsumerCommiterMiddleware` не проверяется: `ArrayRepositorySource` хранит все попытки.

Заметки:
- `benchDispatchCommiterMiddleware` создаёт сообщение с уникальным offset на каждый вызов — вычитайте `benchCreateMessage`.
  Рост памяти там — это `ArrayRepositorySource` (хранит все попытки), а не утечка пакета.

## Добавить бенчмарк

Класс `*Bench.php` в `benchmarks/` (namespace `KafkaBus\Benchmarks`), подготовка — в методе из `#[BeforeMethods]`,
измеряемые методы начинаются с `bench`, число повторов — `#[Revs]`.
