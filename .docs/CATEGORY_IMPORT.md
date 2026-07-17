# Воспроизводимый импорт категорий

Документ описывает source contract, preview/apply/rollback workflow и правила
безопасности для `catalog:import-categories`.

## Правовой статус Prom.ua

Проверка выполнена `2026-07-17` по официальным документам:

- [Угода користувача Prom.ua](https://prom.ua/ua/terms-of-use);
- [справка Prom.ua о robots.txt](https://support.prom.ua/hc/uk/articles/360005257938).

Пункт 4.4 пользовательского соглашения запрещает автоматизированный доступ,
получение, копирование и отслеживание содержимого маркетплейса. Пункт 5.8 не
передает пользователю права на использование контента других пользователей.
Разрешение индексации в `robots.txt` не является лицензией на копирование или
переиспользование каталога.

Решение проекта:

- network import `prom.ua` по умолчанию отключен;
- включение возможно только после отдельного письменного разрешения или
  лицензионного соглашения;
- идентификатор разрешения фиксируется в
  `CATALOG_IMPORT_PROM_RIGHTS_REFERENCE`;
- договор, письмо и другие конфиденциальные документы не хранятся в Git;
- до получения разрешения используются только синтетические test fixtures или
  утвержденные project-owned/licensed snapshots.

Это техническая фиксация ограничений источника, а не юридическая консультация.
Владелец продукта должен сохранить правовое основание в принятой системе
документооборота.

## Source contract

Каждая импортируемая категория имеет:

- `external_source`: стабильный идентификатор источника;
- `external_id`: стабильный ID категории внутри источника;
- `source_last_seen_at`: время последнего примененного snapshot.

Пара `external_source + external_id` уникальна. Название, порядок и родитель не
участвуют в identity, поэтому rename и move обновляют существующую строку.

Prom DOM adapter сначала читает `data-category-id`. Fallback `path:<href>` нужен
только для legacy/local fixtures и не считается достаточным для network import.
Network source с `require_explicit_external_ids=true` принимается только при
наличии явных `id:<value>` у всех узлов.

Source version описывает версию parser/fixture contract, а не дату запуска.
При изменении DOM-селекторов или semantics external ID версия должна измениться.

## Manifest

Preview создает запись `category_import_manifests`:

- `source` и `source_version`;
- URL или безопасное имя fixture;
- SHA-256 checksum нормализованного snapshot;
- полный набор source records;
- diff с действиями `create`, `update`, `deactivate`;
- статистику `created`, `updated`, `deactivated`, `unchanged`;
- статус `preview`, `applied` или `rolled_back`.

Checksum считается по нормализованным records, поэтому одинаковый source
snapshot дает одинаковый checksum независимо от времени запуска.

Apply выполняется транзакционно и только для `preview`. Перед изменением данных
проверяется, что категории все еще совпадают с состоянием на момент preview.
Если администратор или другой import изменил каталог, manifest считается
устаревшим и не применяется частично.

## Fixture directories

CLI читает HTML только из разрешенных директорий:

```text
$PROJECT_ROOT/snabix-backend/storage/app/imports/categories
$PROJECT_ROOT/snabix-backend/tests/Fixtures/catalog
```

Symlink, ведущий за пределы этих директорий, отклоняется после `realpath`.
Допускаются `.html` и `.htm`; размер ограничен
`CATALOG_IMPORT_MAX_RESPONSE_BYTES`.

Fixtures в `tests/Fixtures/catalog` синтетические. Они проверяют DOM contract,
но не являются данными для production-каталога.

## Preview

Утвержденный snapshot сначала помещается в
`storage/app/imports/categories`.

```bash
php artisan catalog:import-categories \
  --source=licensed-catalog \
  --source-version=licensed-v1 \
  --fixture=storage/app/imports/categories/catalog.html
```

Команда выводит:

- manifest UUID;
- checksum;
- количество create/update/deactivate/unchanged;
- строки diff с прежним и новым parent reference.

Preview не изменяет категории. `--dry-run` оставлен как совместимый alias
preview-режима; audit manifest при этом сохраняется.

## Approval и apply

После проверки diff:

```bash
php artisan catalog:import-categories \
  --apply=<manifest-uuid> \
  --approve
```

Можно создать preview и применить его одним запуском, но `--approve` все равно
обязателен:

```bash
php artisan catalog:import-categories \
  --source=licensed-catalog \
  --source-version=licensed-v1 \
  --fixture=storage/app/imports/categories/catalog.html \
  --approve
```

Элементы, отсутствующие в новом snapshot, не удаляются. Импортированные
категории выбранного `external_source` получают `is_active=false`. Ручные
категории без `external_source` и категории других источников не затрагиваются.

## Rollback

```bash
php artisan catalog:import-categories \
  --rollback=<manifest-uuid> \
  --approve
```

Rollback также проверяет отсутствие последующих изменений:

- update/deactivate восстанавливают имя, родителя, порядок, slug и active state;
- созданные manifest категории деактивируются вместо физического удаления;
- объявления и другие внешние ссылки на категории не удаляются каскадно;
- повторный rollback одного manifest запрещен статусом.

## Network source

Network import остается выключенным:

```dotenv
CATALOG_IMPORT_PROM_NETWORK_ENABLED=false
CATALOG_IMPORT_PROM_RIGHTS_REFERENCE=
```

После получения письменного разрешения:

```dotenv
CATALOG_IMPORT_PROM_NETWORK_ENABLED=true
CATALOG_IMPORT_PROM_RIGHTS_REFERENCE=LEGAL-OR-CONTRACT-REFERENCE
```

Дополнительные ограничения:

- только HTTPS;
- exact host allowlist, без suffix matching;
- credentials, query, fragment и нестандартные порты запрещены;
- IP-address host запрещен;
- redirects отключены;
- response size и Content-Type проверяются;
- parser обязан вернуть explicit stable IDs.

## Проверки

```bash
task check
```

Автоматические тесты фиксируют:

- parser contract на локальных fixtures без HTTP;
- блокировку network source без правового reference;
- HTTPS host allowlist;
- preview без изменений каталога;
- обязательный `--approve`;
- повторный idempotent import;
- rename/move без дублирования UUID;
- deactivate missing;
- rollback предыдущего состояния.
