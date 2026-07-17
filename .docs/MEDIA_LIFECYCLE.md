# Жизненный цикл медиа

Snabix использует Spatie Media Library с собственным генератором путей и отдельными командами очистки.

## Основные файлы

- `app/Media/Infrastructure/Models/EloquentMedia.php`
- `app/Media/Infrastructure/Support/MediaPathGenerator.php`
- `app/Media/Application/Services/MediaStorageService.php`
- `app/Listing/Application/Services/ListingMediaService.php`
- `app/Shared/CLI/CleanupStorageCommand.php`
- `app/Media/CLI/CleanupOrphanFilesCommand.php`
- `config/media-library.php`
- `config/storage-cleanup.php`

## Структура storage

Операции через `MediaStorageService` хранят постоянную версию по типу, коллекции, UUID медиа и UUID операции:

```text
storage/app/public/images/listing-images/{media_uuid}/versions/{operation_uuid}/photo.jpg
storage/app/public/documents/{collection}/{media_uuid}/versions/{operation_uuid}/file.pdf
storage/app/public/videos/{collection}/{media_uuid}/versions/{operation_uuid}/video.mp4
storage/app/public/files/{collection}/{media_uuid}/versions/{operation_uuid}/file.bin
```

`media.storage_key` хранит точный key оригинала. Для записей, созданных до появления этого поля, `NULL` означает legacy-путь `{type}/{collection}/{media_uuid}/{file_name}`. Поэтому миграция не требует синхронного перемещения существующих пользовательских файлов.

Потоки, которые пока используют Spatie `addMedia*` напрямую, также продолжают работать с legacy-путями:

```text
storage/app/public/images/listing-images/{media_uuid}/photo.jpg
storage/app/public/images/avatar/{media_uuid}/avatar.png
storage/app/public/images/category-icons/{media_uuid}/icon.png
```

Конверсии versioned-файла:

```text
storage/app/public/images/{collection}/{media_uuid}/versions/{operation_uuid}/conversions/*
storage/app/public/images/{collection}/{media_uuid}/versions/{operation_uuid}/responsive-images/*
```

Источник истины для пути: `MediaPathGenerator`.

## Отказоустойчивая запись

`MediaStorageService` не считает PostgreSQL и filesystem одной транзакцией. Create, replace и move выполняются в следующем порядке:

1. исходный объект читается и получает SHA-256 checksum и размер;
2. объект копируется на `MEDIA_STAGING_DISK` в `MEDIA_STAGING_PREFIX/{operation_uuid}`;
3. staging-копия повторно проверяется по checksum и размеру;
4. staging-копия записывается в новый versioned permanent key и снова проверяется;
5. короткая DB transaction сохраняет новый `storage_key`;
6. только after-commit удаляются исходный временный upload, staging и предыдущая версия.

Replace не перезаписывает старый key, даже если имя файла не изменилось. UUID самой media-записи остается стабильным, а физическая версия меняется через `operation_uuid`.

При copy или DB exception новая staging/permanent версия компенсируется, а старый файл и исходный upload остаются доступны. Если delete временно недоступен, `CleanupMediaStorageObjectsJob` отправляется в очередь `MEDIA_CLEANUP_QUEUE`. Job перед каждым удалением проверяет, что key больше не используется записью `media`, поэтому повторный запуск безопасен.

Worker должен обслуживать обе очереди:

```dotenv
QUEUE_WORK_QUEUES=notifications,media-maintenance
```

## Таблица `media`

Важные поля:

- `uuid`
- `storage_key`
- `model_type`
- `model_id`
- `collection_name`
- `file_name`
- `disk`
- `conversions_disk`
- `size`
- `generated_conversions`
- `responsive_images`
- `media_type`
- `visibility`
- `order_column`

Запись в БД является источником истины для постоянного медиафайла.

## Изображения объявления

Коллекция:

```text
listing-images
```

Лимит:

```text
8 изображений на объявление
```

Операции:

- загрузить изображения;
- удалить одно изображение;
- изменить порядок;
- сделать изображение главным.

Backend-сервис:

```text
App\Listing\Application\Services\ListingMediaService
```

Маршруты:

- `POST /api/v1/listings/{listingId}/media`
- `PATCH /api/v1/listings/{listingId}/media/reorder`
- `PATCH /api/v1/listings/{listingId}/media/{mediaId}/main`
- `DELETE /api/v1/listings/{listingId}/media/{mediaId}`

## Временные файлы

Временные файлы можно удалять по retention:

- `storage/app/private/filament-media-temp`
- `storage/app/private/filament-category-icons-temp`
- `storage/app/private/media-staging`
- `storage/app/private/livewire-tmp`
- `storage/app/public/livewire-tmp`
- `storage/media-library/temp`
- `storage/debugbar`
- `storage/api-docs`
- `storage/logs`

Dry-run:

```bash
php artisan shared:cleanup-storage --dry-run
```

Реальная очистка:

```bash
php artisan shared:cleanup-storage
```

Эта команда не должна включать постоянную директорию `storage/app/public/images`.

## Orphan media

Постоянные медиа нельзя удалять только потому, что они старые.

Файл считается orphan, если:

- он находится внутри media-root `images`, `documents`, `videos`, `files`;
- он старше grace period;
- его media-директория не соответствует ни одной записи в таблице `media`.

Dry-run:

```bash
php artisan media:cleanup-orphans
```

Удаление после проверки:

```bash
php artisan media:cleanup-orphans --days=7 --force
```

Ограничение по disk:

```bash
php artisan media:cleanup-orphans --disk=public --days=14
```

## Почему orphan cleanup ручной

Постоянные медиа — пользовательские данные. Автоматическое удаление опасно после:

- прерванного deploy;
- временного restore базы;
- случайной очистки таблицы;
- неудачной media migration;
- задержанной conversion job.

Поэтому команда по умолчанию работает в dry-run и требует `--force`.

## Правила удаления

Можно:

- удалять временные uploads по retention;
- удалять logs/docs/debugbar по retention;
- удалять orphan persistent media после dry-run;
- удалять media через доменные сервисы.

Нельзя:

- удалять `storage/app/public/images` по возрасту;
- удалять media-директории без проверки БД;
- запускать orphan cleanup во время DB-инцидента без понимания причины;
- использовать `rm -rf storage/app/public/images/*`.

## Тесты

- `tests/Feature/Media/MediaStorageServiceTest.php`
- `tests/Feature/Listing/ListingMediaUploadTest.php`
- `tests/Feature/CLI/CleanupStorageCommandTest.php`
- `tests/Feature/CLI/CleanupOrphanMediaCommandTest.php`

## Будущие улучшения

- Filament-страница со списком orphan-кандидатов.
- JSON-отчет dry-run.
- S3-compatible cleanup, если media переедут в object storage.
- Команда регенерации conversions перед удалением подозрительных conversion-файлов.
