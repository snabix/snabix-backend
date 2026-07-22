# Импорт регионов и городов России

Документ описывает воспроизводимый и отказоустойчивый workflow команды
`location:import-russia`.

## Source contract

Импорт принимает два JSON-массива: регионы и города. Стабильной identity записи
служит `id` источника, который сохраняется как `kladr_id`. Название, slug и
принадлежность города региону могут меняться без создания дубля.

Файлы `russia-regions.json` и `russia-cities.json` намеренно не хранятся в Git.
В production разрешено использовать только утвержденный snapshot с известным
владельцем, датой получения и правовым основанием. SHA-256 в manifest доказывает
воспроизводимость конкретного файла, но сам по себе не подтверждает право его
использования.

Snapshot считается полным и авторитетным для справочника России. Существующие
регионы и города, которых нет в применяемом snapshot, получают
`is_active=false`. Они не удаляются физически, поэтому связанные адреса и
исторические данные сохраняются. Перед применением уменьшенного snapshot всегда
нужно выполнить `--dry-run` и проверить счетчики deactivation.

## Двухфазная обработка

### 1. Prepare

- `halaxa/json-machine` читает JSON потоково без загрузки массива целиком;
- записи нормализуются и вставляются в `location_import_staging` пакетами по
  `250` строк;
- проверяются обязательные external IDs и наличие региона для каждого города;
- `location_import_manifests` сохраняет безопасные имена файлов, размеры,
  SHA-256 обоих файлов, объединенную `source_version` и preview statistics;
- полные локальные пути и содержимое source-файлов в manifest не сохраняются.

### 2. Promotion

- manifest блокируется и переводится из `preview` в `applying`;
- регионы и города применяются batch `upsert` по `kladr_id`;
- отсутствующие source records мягко деактивируются;
- вся смена production-таблиц выполняется в одной PostgreSQL transaction;
- только после commit версия location cache увеличивается один раз;
- staging rows удаляются после успешного применения.

Если нормализация, relation check или promotion завершается ошибкой, manifest
получает статус `failed`. Ошибка после region upsert откатывает и регионы, и
города, поэтому публичный справочник не остается в частично обновленном
состоянии.

## Конкурентность и прерывание

На весь prepare/apply workflow берется distributed lock
`location-import:russia`. Параллельный запуск ожидает lock не более `5` секунд;
TTL lock равен `300` секундам.

При аварийном завершении во время prepare production-таблицы еще не менялись.
Незавершенные `preparing`/`preview` manifests старше часа автоматически
помечаются `failed`, а их staging rows удаляются следующим запуском. При
прерывании promotion PostgreSQL откатывает транзакцию целиком.

## Команды

Сначала посмотреть diff statistics без изменения справочника:

```bash
php artisan location:import-russia --dry-run
```

Применить проверенный snapshot:

```bash
php artisan location:import-russia
```

Передать файлы явно:

```bash
php artisan location:import-russia \
  --regions=storage/app/imports/locations/russia-regions.json \
  --cities=storage/app/imports/locations/russia-cities.json
```

`--fresh` физически очищает `cities` и `regions` внутри promotion transaction.
Режим предназначен только для первичного bootstrap пустой базы и может быть
отклонен внешними ключами при наличии пользовательских адресов. Для обычного
обновления используется reconcile без `--fresh`.

## Performance budget

Автоматический тест использует синтетический snapshot той же cardinality:

- `83` региона;
- `1102` города;
- peak memory не выше `96 MiB`;
- общее время не выше `30 s`;
- не более `60` SQL-запросов, относящихся к manifest/staging/regions/cities.

Локальный benchmark утвержденных файлов от `2026-07-22`:

- prepare: `117 ms`;
- полный import: `243 ms`;
- peak memory: `74,448,896` bytes (`71 MiB`);
- SQL query count: `36`.

Числа зависят от runtime и не являются production SLO. Их назначение - поймать
возврат к `json_decode` всего файла, per-row persistence или per-row cache bump.

## Проверки

```bash
php artisan test tests/Feature/Location/RussiaLocationImportCommandTest.php
php artisan test tests/Feature/Location/RussiaLocationImportPerformanceTest.php
task check
```

Тесты фиксируют checksum/version manifest, idempotent update, soft deactivation,
dry-run без production writes, единичную cache invalidation, rollback после
частично начатой promotion и очистку abandoned staging.
