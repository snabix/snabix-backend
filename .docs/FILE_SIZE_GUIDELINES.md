# Контроль размера backend-файлов

Документ фиксирует крупные backend-файлы, которые можно оставить временно, но нельзя незаметно раздувать дальше. Цель правила - не механическое дробление, а понятная ответственность файлов и безопасные ревью.

## Правило

Если tracked-файл меняется функционально, нужно одно из двух:

- декомпозировать файл по ответственности;
- оставить короткое обоснование, почему файл временно остается крупным, и при необходимости обновить baseline в `scripts/check-file-sizes.php`.

Проверка запускается через:

```bash
task files:size
task check
```

## Текущий baseline

| Файл | Строк | Решение |
| --- | ---: | --- |
| `app/Shared/CLI/CleanupStorageCommand.php` | 357 | Rename `2026-07-17` не менял cleanup workflow; разделить при следующем функциональном изменении |
| `app/News/Filament/Resources/NewsPosts/Schemas/NewsPostForm.php` | 349 | Разделить schema sections при следующем изменении формы |
| `app/Listing/Infrastructure/Services/ListingAttributeValueSynchronizer.php` | 331 | Разделить синхронизацию, pruning и normalization при следующем изменении сервиса |

## Рекомендуемая декомпозиция

### `CleanupStorageCommand.php`

Возможное направление:

```text
app/Shared/CLI/CleanupStorageCommand.php
app/Shared/Application/Services/StorageCleanupPlanner.php
app/Shared/Application/Services/StorageCleanupExecutor.php
app/Shared/Application/DTO/StorageCleanupSummary.php
```

### `NewsPostForm.php`

Возможное направление:

```text
app/News/Filament/Resources/NewsPosts/Schemas/NewsPostForm.php
app/News/Filament/Resources/NewsPosts/Schemas/NewsPostMainSection.php
app/News/Filament/Resources/NewsPosts/Schemas/NewsPostSeoSection.php
app/News/Filament/Resources/NewsPosts/Schemas/NewsPostContentBlocksSection.php
```

### `ListingAttributeValueSynchronizer.php`

Возможное направление:

```text
app/Listing/Infrastructure/Services/ListingAttributeValueSynchronizer.php
app/Listing/Infrastructure/Services/ListingAttributeValueNormalizer.php
app/Listing/Infrastructure/Services/ListingAttributeValuePruner.php
app/Listing/Infrastructure/Services/ListingAttributeValueUpserter.php
```
