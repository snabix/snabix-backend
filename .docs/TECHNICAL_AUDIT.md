# Технический аудит Snabix

Дата актуализации: 2026-07-16  
Область: `snabix-backend`, `snabix-frontend`, `snabix-telegram-bot`  
Рабочие ветки на момент аудита: backend `dev__implement-review-users`, frontend `dev__listings`, bot `main`  
Назначение: единый живой checklist качества, безопасности и развития продукта.

## Как вести аудит

- `[ ]` - задача не начата.
- `[~]` - задача начата, но критерий готовности выполнен не полностью.
- `[x]` - задача выполнена и подтверждена проверками.
- У закрытого пункта нужно указывать дату, коммит каждого затронутого репозитория и фактически выполненные проверки.
- Нельзя ставить `[x]` только по факту написания кода: должны быть выполнены все критерии готовности пункта.
- При изменении приоритета сохраняется ID задачи и добавляется короткое объяснение.

Приоритеты:

- `P0` - блокирует безопасный staging или публичный запуск.
- `P1` - необходимо закрыть до стабильного публичного релиза.
- `P2` - важно для сопровождаемости, производительности и роста.
- `P3` - продуктовый или архитектурный следующий этап после стабилизации ядра.

## Методика и границы

Проверено:

- 31 Markdown-файл трех проектов, 4321 строка документации;
- прежний межрепозиторный аудит, 310 строк;
- 619 PHP-файлов backend, 31 456 строк production PHP и 150 найденных тестовых методов;
- 348 TypeScript/TSX-файлов frontend, 23 639 строк production-кода и 40 test/spec-файлов;
- 23 Python-файла bot, 606 строк production-кода и фактически один pytest-тест;
- API-маршруты, миграции, auth/session, объявления, медиа, уведомления, отзывы, каталог, локации, Filament, frontend API/state/UI, bot client/handlers;
- Docker Compose, Dockerfile, Taskfile, GitHub Actions и release/security checklist каждого репозитория;
- locked-зависимости командами `composer audit`, `npm audit` и `pip-audit`.

Не проверено в рамках локального аудита:

- реальное staging/production-окружение, DNS, TLS, cookies между фактическими доменами;
- нагрузочный профиль на production-подобном объеме данных;
- backup/restore на отдельном окружении;
- история Git на секреты специализированным scanner;
- юридическая корректность текстов профильным юристом;
- доступность внешнего источника категорий и право долгосрочно использовать его данные.

Эти ограничения не означают, что соответствующие зоны работают неверно. Они означают, что готовность пока не доказана.

## Подтвержденный baseline

- [x] `BASE-001` Backend quality gate проходит: file-size guard, PHP CS Fixer dry-run, PHPStan, Scramble analysis, 151 PHPUnit-тест и 730 assertions. Проверено 2026-07-16.
- [x] `BASE-002` Frontend проходит file-size guard, ESLint, обычный и полный typecheck, 29 Vitest-файлов и 111 тестов, production build, 31 Playwright E2E-тест в Chromium. Проверено 2026-07-16.
- [x] `BASE-003` Telegram bot проходит Ruff format/lint, strict mypy и pytest. Ограничение: pytest выполняет только один тест конфигурации. Проверено 2026-07-16.
- [x] `BASE-004` Рабочие деревья backend и frontend перед созданием аудита были чистыми и синхронизированными со своими remote-ветками. Bot был чистым, но `main` уже опережал `origin/main` на один существующий коммит `c0b6c94`.
- [x] `BASE-005` В документации проектов не осталось абсолютных путей старого пользователя; используются относительные пути и `$PROJECT_ROOT`.
- [x] `BASE-006` Устаревший корневой аудит от 2026-07-08 удален, а единый актуальный checklist перенесен в отслеживаемый Git файл `.docs/TECHNICAL_AUDIT.md`. Выполнено 2026-07-16.

Локальные версии отличаются от целевых runtime:

- backend проверялся локальным PHP 8.5.8, тогда как `composer.json`, Docker и CI ориентированы на PHP 8.3;
- frontend и CI закреплены на Node 22 через `.nvmrc`;
- bot локально находится в Python 3.9.6/LibreSSL, а CI использует Python 3.11.

Зеленые проверки означают отсутствие обнаруженных статических и тестовых регрессий. Они не отменяют найденные дефекты поведения и уязвимые версии зависимостей.

## Резюме

Snabix уже имеет хорошую основу для модульного marketplace: Laravel-монолит разделен по предметным модулям, публичные и приватные DTO в основном разведены, frontend использует строгий TypeScript и runtime-схемы, bot обращается к backend через service API, а CI существует во всех трех репозиториях.

До production-ready состояния проект пока не доведен. Главные причины:

1. В locked-зависимостях всех трех стеков есть известные security advisories, включая high severity.
2. Favorites API использует приватный mapper объявления и может раскрывать контактные и модерационные поля чужого объявления.
3. Публикация объявления не устанавливает `published_at`/`expires_at`, а публичная выдача не исключает просроченные записи.
4. Media replacement сочетает транзакцию БД с необратимыми файловыми операциями и допускает потерю старого файла при ошибке.
5. Отзывы можно оставить без доказанного взаимодействия или сделки, поэтому рейтинг легко сфальсифицировать.
6. Нет production image/deployment pipeline, scheduler-процесса, проверенного backup/restore и наблюдаемости очередей.
7. Публичная витрина frontend загружается после hydration и пока слабо подготовлена к SEO, LCP и link previews.
8. Bot функционально минимален и почти не защищен тестами от сетевых и Telegram-specific отказов.

Рекомендуемая архитектурная позиция на ближайший этап: сохранять модульный монолит, PostgreSQL и текущие фреймворки; не вводить микросервисы, Elasticsearch, Kubernetes или ML-рекомендации до исправления P0, появления измеримого объема и подтвержденного требования.

## Аудит документации

### Backend

| Файл | Состояние | Что требуется |
| --- | --- | --- |
| `.docs/AGENTS.md` | Частично актуален | Правила полезны, но код уведомлений и Review уже нарушает заявленный `Request -> Input -> Handler -> Output -> Response`; убрать устаревшее описание media/moderation как будущих зон. |
| `.docs/API_DTO_CONTRACTS.md` | Устарел | Добавить `regionId`, `cityId`, `region`, `city`, `isNegotiable`, актуальные sort; исправить envelope 401/419 с `error` на фактический `code`; включить reviews и export. |
| `.docs/ARCHITECTURE.md` | Частично актуален | Привести topology, очередь, scheduler, bot и deployment к факту; честно назвать архитектуру pragmatic modular layered, пока Domain зависит от Eloquent/Laravel. |
| `.docs/FILE_SIZE_GUIDELINES.md` | Политика актуальна, baseline устарел | Обновить список крупных файлов и требовать ID этого аудита для исключений. |
| `.docs/LISTING_LIFECYCLE.md` | Намерение сильнее реализации | Отметить, что публикационные даты, expiration, sold/completed и re-moderation еще не реализованы. |
| `.docs/LOCAL_DEVELOPMENT.md` | В основном актуален | Явно разделить Mailpit SMTP `1025` и UI `8025`, описать scheduler и поддерживаемую PHP 8.3. |
| `.docs/MEDIA_LIFECYCLE.md` | Частично актуален | Добавить failure/compensation model, backup consistency, private/public disk policy и conversion delivery contract. |
| `.docs/NOTIFICATIONS_ARCHITECTURE.md` | Частично актуален | Зафиксировать фактическую synchronous DB delivery, отсутствие outbox, retry/DLQ policy и не реализованные event types. |
| `.docs/RELEASE_CHECKLIST.md` | Неполон | Добавить dependency audits, frontend build, scheduler/worker heartbeat, migration rollback, restore drill, staging smoke и security headers. |
| `.docs/SECRETS.md` | Хорошая база | Отличить self-test guard от проверки реального deployment env; добавить историю Git, ротацию service credentials и владельца секрета. |
| `.docs/TESTING_STRATEGY.md` | Хорошая база | Добавить privacy contract tests, concurrency tests, query budgets, queue failure и restore scenarios. |
| `CHANGELOG.md` | Сильно устарел | Существующий `Unreleased` не отражает значительную часть июльских коммитов, а versioned entries расположены непоследовательно; актуализировать и обновлять в PR. |
| `README.md` | Слишком краткий | Добавить назначение, поддерживаемые runtime, быстрый запуск, quality gate, ссылки на архитектуру и этот аудит. |

### Frontend

| Файл | Состояние | Что требуется |
| --- | --- | --- |
| `AGENTS.md` | Правила полезны, код частично расходится | Header находится в `shared`, хотя импортирует entities/features; чрезмерно используются client boundaries и `...Action` props. |
| `CLAUDE.md` | Актуален как pointer | Содержит только ссылку на `AGENTS.md`; отдельного содержания не требует. |
| `README.md` | Устарел | Остался почти стандартным Create Next App README и не описывает Snabix, env, архитектуру и проверки. |
| `docs/ARCHITECTURE.md` | Частично актуален | Отразить реальную структуру, RSC/client policy, server-state strategy, notification polling и public SEO flow. |
| `docs/FILE_SIZE_GUIDELINES.md` | Политика актуальна, список дрейфует | Обновить baseline: privacy 322, about 323, listings 293, sessions 279, category store 267, notifications 265, share dialog 257. |
| `docs/LOCAL_DEVELOPMENT.md` | В основном актуален | Добавить production build в обязательный local gate и объяснить backend/bot sibling setup. |
| `docs/RELEASE_CHECKLIST.md` | Частично актуален | Автоматизировать существующий performance budget; добавить multi-browser/mobile/a11y, dependency audit и real-backend smoke. |
| `docs/SANCTUM_SESSION_PRODUCTION.md` | Хороший runbook, не доказательство | Сохранять checklist, но не считать задачу закрытой без отчета с фактического staging. |
| `docs/SECRETS.md` | Актуален | Добавить правило о server-only переменных и запрет чувствительных значений с `NEXT_PUBLIC_`. |
| `docs/SESSION_EXPIRATION_UX.md` | Узкий и частично дублирующий | После реализации перенести устойчивые правила в architecture/testing и удалить отдельный временный spec. |
| `docs/TESTING.md` | Дублирует strategy | Объединить команды и уровни тестирования с `TESTING_STRATEGY.md`, оставить один источник истины. |
| `docs/TESTING_STRATEGY.md` | Намерение шире факта | Chromium mocks покрыты хорошо, но нет WebKit/Firefox/mobile/a11y и отдельного staging contract smoke. |

### Telegram bot

| Файл | Состояние | Что требуется |
| --- | --- | --- |
| `README.md` | В основном актуален для MVP | Явно обозначить, что production deployment/readiness и пользовательские уведомления еще отсутствуют. |
| `docs/BOT_ARCHITECTURE.md` | Частично актуален | Добавить timeout/retry, webhook readiness, offset policy, structured logs/metrics и service credential model. |
| `docs/LOCAL_DEVELOPMENT.md` | Воспроизводимость неполная | Выбрать Python 3.11/3.12, добавить lock-файл и убрать двойное ведение диапазонов в `pyproject.toml`/requirements. |
| `docs/RELEASE_CHECKLIST.md` | Неполон | Добавить dependency audit, container build, readiness, webhook secret enforcement, update preservation и rollback. |
| `docs/SECRETS.md` | Хорошая база | Сделать webhook secret обязательным в production и описать scoped service token/rotation. |
| `docs/TESTING_STRATEGY.md` | План не соответствует покрытию | Фактически есть только один settings test; превратить перечисленные уровни в измеримый test plan. |

## Ранее закрытая основа

- [x] `DONE-001` Воспроизводимое backend test-окружение и `task check`. Закрыто 2026-07-08.
- [x] `DONE-002` Удалены документационные абсолютные пути старого пользователя. Закрыто 2026-07-08.
- [x] `DONE-003` Добавлен file-size baseline backend/frontend. Закрыто 2026-07-08.
- [x] `DONE-004` Синхронизирован фильтр `isNegotiable` frontend/backend и E2E fixture. Закрыто 2026-07-08.
- [x] `DONE-005` Node/npm policy закреплена `.nvmrc`, engines и CI. Закрыто 2026-07-09.
- [x] `DONE-006` Имя frontend env унифицировано до `NEXT_PUBLIC_API_URL`. Закрыто 2026-07-09.
- [x] `DONE-007` Реализованы responsive listing media conversions и AVIF/WebP policy frontend. Закрыто 2026-07-09.
- [x] `DONE-008` Добавлено кэширование справочников категорий и локаций. Закрыто 2026-07-09.
- [x] `DONE-009` Усилены production secret checklist и self-test guards backend/bot. Закрыто 2026-07-09, расширено 2026-07-10.
- [x] `DONE-010` Добавлены moderation actions объявлений и обязательные site/email notifications. Закрыто 2026-07-09.
- [x] `DONE-011` Зафиксирован ручной performance budget public listings. Закрыто 2026-07-10; автоматизация остается в `P1-CI-003`.
- [x] `DONE-012` Добавлен минимальный reviews API и seller rating aggregate. Закрыто 2026-07-12; eligibility и lifecycle остаются в `P0-TRUST-001`.
- [x] `DONE-013` Исправлено отображение существующих media в Filament forms. Закрыто 2026-07-16.

## P0 - Блокеры релиза

### Безопасность зависимостей

- [x] `P0-SEC-001` Обновить уязвимые locked-зависимости backend и включить audit в CI.
  - Факт: `composer audit --locked` от 2026-07-16 нашел 12 advisories в 8 пакетах; `--no-dev` - 10 advisories в 7 production-пакетах.
  - Критичные версии: Filament `5.6.2` уязвим к `CVE-2026-48505` high и нескольким medium; Guzzle `7.11.0`, PSR-7 `2.11.0`, phpseclib `3.0.52`, Symfony YAML `7.4.8` также имеют advisories.
  - Риск: компрометация MFA recovery flow, временные uploads на auth pages, user enumeration, XSS в image values, HTTP parsing/proxy и SSRF-сценарии.
  - Где смотреть: `composer.json`, `composer.lock`, `.github/workflows/ci.yml`, Filament auth/upload/image configuration.
  - План: обновить сначала patch/minor-версии; прочитать upgrade notes; проверить Filament login, Shield permissions, media forms и moderation actions; запретить merge при production advisory high/critical.
  - Критерий готовности: `composer audit --locked --no-dev` не содержит high/critical и имеет документированные исключения для остальных; `task check` и admin smoke проходят; audit запускается CI.
  - Выполнено 2026-07-16, реализация: `4d15562`.
  - Решение: Filament обновлен до `5.6.8`, Guzzle до `7.14.2`, PSR-7 до `2.12.5`, phpseclib до `3.0.55`, Symfony YAML до PHP 8.3-совместимой `7.4.14`; lock-файл рассчитан в целевом PHP 8.3 runtime, Filament assets переопубликованы.
  - Контроль: CI и `task deps:audit` выполняют `composer audit --locked --no-dev --format=summary --abandoned=fail`. Gate строже минимального критерия и блокирует любую production advisory или abandoned package. Исключения не добавлены, потому что production и полный Composer audit возвращают ноль advisories.
  - Проверки: `composer validate --strict --no-check-publish`, Docker PHP 8.3 `composer install --dry-run`, production/full Composer audits, `task check` (151 тест, 730 assertions) и Filament admin smoke (2 теста, 12 assertions) прошли.

- [x] `P0-SEC-002` Обновить уязвимые locked-зависимости frontend и включить audit в CI.
  - Факт: production `npm audit --omit=dev` нашел 3 уязвимости: 2 high и 1 moderate. Прямой Next `16.2.4` имеет исправление в `16.2.10`; transitive `form-data 4.0.5` и Next `postcss 8.4.31` уязвимы. Полный audit также отмечает dev `undici 7.27.2` через jsdom.
  - Риск: DoS, middleware/proxy bypass, SSRF/cache poisoning/XSS в Next, CRLF injection в multipart.
  - Где смотреть: `package.json`, `package-lock.json`, `.github/workflows/ci.yml`, `next.config.ts`.
  - План: обновить Next/eslint-config-next одной версией, Axios/transitive form-data и jsdom; не использовать `npm audit fix --force`; проверить release notes и lockfile diff.
  - Критерий готовности: production audit без high/critical; lint, оба typecheck, tests, build и полный E2E проходят; CI выполняет `npm audit --omit=dev --audit-level=high`.
  - Выполнено 2026-07-16, frontend-реализация: `f6954d2`.
  - Решение: Next и eslint-config-next обновлены до `16.2.10`, Axios до `1.18.1`, transitive form-data до `4.0.6`, а undici в актуальном jsdom `29.1.1` до `7.28.0`; lock-файл рассчитан npm `10.9.0` под Node `22.23.1` без `--force` и transitive overrides.
  - Контроль: CI и `npm run audit:prod` выполняют `npm audit --omit=dev --audit-level=high`; итоговый production/full audit содержит `0 high`, `0 critical`.
  - Исключение: `GHSA-qx2v-qp2m-jg93` остается как две moderate-записи через Next и закрепленный им PostCSS `8.4.31`. Применимость, компенсирующие меры, владелец и срок пересмотра до 2026-08-16 описаны в frontend `docs/RELEASE_CHECKLIST.md`; неподдерживаемый откат Next через `npm audit fix --force` не применяется.
  - Проверки: clean `npm ci`, file-size guard, ESLint, обычный и полный typecheck, 29 Vitest-файлов/111 тестов, production build и 31 Playwright E2E-тест прошли на Node `22.23.1`.

- [ ] `P0-SEC-003` Зафиксировать безопасный Python dependency set для bot.
  - Факт: текущий локальный диапазон разрешил `aiohttp 3.12.15` и `python-dotenv 1.2.1`; `pip-audit` нашел известные advisories с исправлениями в `aiohttp 3.14.1` и `python-dotenv 1.2.2`. Общий отчет содержит 44 advisories в 9 пакетах, но часть пакетов установлена самим audit/dev tooling и не входит в runtime bot.
  - Риск: диапазоны без lock дают разные и потенциально уязвимые окружения; локальный Python 3.9/LibreSSL уже расходится с CI 3.11.
  - Где смотреть: `pyproject.toml`, `requirements*.txt`, `.github/workflows/ci.yml`, `.venv` только как локальное окружение.
  - План: выбрать Python 3.11 или 3.12, создать hash-locked runtime/dev набор через `uv lock` или `pip-tools`, обновить aiohttp/python-dotenv, сканировать именно lock/runtime set.
  - Критерий готовности: clean install воспроизводит версии; runtime audit без high/critical; `task check` проходит на той же Python version локально и в CI.

### Приватность и целостность данных

- [ ] `P0-PRIV-001` Исключить приватные поля из favorites API.
  - Факт: `AddListingFavoriteHandler`, `RemoveListingFavoriteHandler` и `ListFavoriteListingsHandler` используют `ListingPayloadMapper`, предназначенный для owner/private view, тогда как публичные endpoints используют `PublicListingPayloadMapper`.
  - Риск: авторизованный пользователь может получить `userId`, контактные поля, `rejectionReason` и другие приватные данные чужого опубликованного объявления через favorite endpoints. List также может создавать N+1 на owner relation.
  - Где смотреть: `app/Listing/Application/UseCases/*ListingFavorite*`, `app/Listing/Application/Support/ListingPayloadMapper.php`, `PublicListingPayloadMapper.php`, `tests/Feature/Listing/ListingFavoriteTest.php`.
  - План: возвращать public card DTO для чужих favorites; если owner нужен собственному объявлению, выбирать projection по authorization context, а не по endpoint случайно.
  - Критерий готовности: feature-тесты add/remove/list явно проверяют отсутствие всех private/contact/moderation полей; query-count test исключает N+1; frontend contract остается зеленым.

- [ ] `P0-DATA-001` Сделать media create/replace/move отказоустойчивыми.
  - Факт: `MediaStorageService` выполняет файловые copy/delete внутри DB transaction. Replace удаляет старый файл до гарантированного сохранения нового; rollback БД не может вернуть удаленный объект storage.
  - Риск: потеря пользовательского файла, orphan objects или запись БД, указывающая на отсутствующий файл.
  - Где смотреть: `app/Media/Application/Services/MediaStorageService.php`, Spatie Media Library operations, orphan cleanup, media tests.
  - План: staged upload в временный key, checksum/size verification, DB commit на новый key, удаление старого объекта только after-commit; для неуспеха - idempotent compensation job. Не считать storage транзакционным вместе с PostgreSQL.
  - Критерий готовности: fault-injection tests на copy/delete/DB exception подтверждают сохранность старого файла и последующую уборку временного; операции идемпотентны.

- [ ] `P0-ACCOUNT-001` Спроектировать и реализовать деактивацию/удаление аккаунта без orphan media и случайной потери обязательных данных.
  - Факт: frontend показывает действия деактивации/удаления, но backend endpoints отсутствуют. DB cascade удалит listings, однако polymorphic Spatie media не гарантирует model events при DB cascade и может оставить строки/файлы.
  - Риск: UI обещает несуществующую функцию; hard delete может нарушить retention требований или оставить персональные файлы.
  - Где смотреть: `settings-account-page.tsx`, users/listings/media migrations, Eloquent model events, notification/review/system log relations.
  - План: определить состояния `active/deactivated/deletion_requested`; отдельный erasure orchestrator перечисляет profile, sessions, listings, media, reviews, favorites, notifications и logs; заранее определить anonymize/delete/retain для каждого типа данных.
  - Критерий готовности: API, confirmation/re-auth, cooling-off period при необходимости, background erasure job, audit event, integration test с файлами storage и документированная retention matrix.

### Core marketplace

- [ ] `P0-LIST-001` Завершить публикационный и expiration lifecycle объявлений.
  - Факт: переход в `PUBLISHED` меняет только status/rejection reason. `published_at` и `expires_at` не устанавливаются; public list/show не фильтруют истекшие записи; scheduler не содержит expire command.
  - Риск: сортировка нестабильна, просроченные объявления остаются публичными, expiration UX и уведомления не могут работать достоверно.
  - Где смотреть: `ListingModerationNormalizer`, `EloquentListingWriter`, `PublicListingQuery`, `ListingStatusTransitionPolicy`, `routes/console.php`.
  - План: доменная policy вычисляет publish/expiry timestamps; определить срок по type/category; scheduler идемпотентно переводит истекшие записи; public projection всегда проверяет срок.
  - Критерий готовности: feature/domain tests на publish, republish, expiry boundary/timezone, archived/rejected; public list/show/favorites не возвращают expired; scheduler test и notification policy готовы.

- [ ] `P0-TRUST-001` Закрыть возможность фиктивных отзывов и определить eligibility для товара и услуги.
  - Факт: любой авторизованный пользователь может оставить отзыв владельцу любого опубликованного объявления. Уникальность только `reviewer_id + listing_id`; факта контакта, заказа или завершенной услуги нет.
  - Риск: рейтинг продавца легко накрутить или атаковать; один отзыв на listing не подходит повторяемой услуге, а множество отзывов без сделок не подходит одноразовому товару.
  - Где смотреть: `UserReviewService`, `user_reviews` migration, Review API, listing types/statuses.
  - План: до полноценного interaction layer либо временно закрыть create API feature flag, либо выдавать одноразовый review eligibility token после подтвержденного взаимодействия. Базовая модель - `listing_interactions`/`orders` с типом, сторонами, status, timestamps и snapshot объявления; отзыв уникален по completed interaction.
  - Критерий готовности: нельзя оставить отзыв без eligible completed interaction; товар допускает один completed sale, повторяемая услуга - несколько отдельных completions; concurrency и deletion/moderation tests обновляют aggregate корректно.

### Production-контур

- [ ] `P0-OPS-001` Создать production runtime вместо использования dev Compose как deployment.
  - Факт: backend Dockerfile не копирует приложение и зависимости, запускается root, сохраняет build packages; Compose bind-mounts source, публикует DB/Redis/RabbitMQ ports и не запускает scheduler. Frontend и bot не имеют production Dockerfile/deploy manifest.
  - Риск: scheduled cleanup/expiry не выполняются; окружение невоспроизводимо и излишне открыто; rollback и horizontal restart не определены.
  - Где смотреть: backend `Dockerfile`, `docker-compose.yml`, Caddyfile; отсутствие Dockerfile/deploy в frontend/bot.
  - План: multi-stage immutable images, non-root runtime, health/readiness, scheduler отдельным process/job, worker graceful shutdown, internal networks, pinned image versions, frontend standalone build, bot image.
  - Критерий готовности: staging разворачивается только из versioned images; migrations отдельным controlled step; app/worker/scheduler/frontend/bot имеют readiness; DB/Redis/RabbitMQ не опубликованы наружу; rollback описан и проверен.

- [ ] `P0-OPS-002` Реализовать согласованный backup/restore DB и object storage.
  - Факт: runbook и автоматический restore drill отсутствуют; media metadata и files должны восстанавливаться в согласованную точку.
  - Риск: backup, который никогда не восстанавливался, не является доказанной защитой; orphan cleanup после несогласованного restore может удалить валидные файлы.
  - Где смотреть: media lifecycle docs, cleanup commands, PostgreSQL/storage provider configuration.
  - План: RPO/RTO, encrypted versioned backups, retention, point-in-time strategy, manifest/checksum, restore в isolated environment; orphan cleanup после restore только dry-run и ручное подтверждение.
  - Критерий готовности: датированный restore report восстанавливает DB и media, проверяет выборку объявлений/изображений/auth; владелец и alert на failed backup назначены.

- [ ] `P0-AUTH-001` Подтвердить production auth/session/CSRF flow на реальном staging.
  - Факт: unit/feature и документационный smoke существуют, но внешний staging не проверялся. `.env.example` все еще задает `FRONTEND_RESET_PASSWORD_URL=${FRONTEND_URL}/auth/reset-password`, хотя route frontend - `/reset-password`.
  - Риск: reset link, SameSite/domain/secure cookies или CORS могут сломаться только на реальных поддоменах; длинная session lifetime усиливает последствия кражи cookie.
  - Где смотреть: frontend Axios/env/docs, backend `cors.php`, `sanctum.php`, `session.php`, `frontend.php`, `.env.example`.
  - План: исправить reset URL; составить environment matrix; проверить sign-up/sign-in/refresh/logout/change password/reset/unsafe CSRF/401/419/other-session termination на HTTPS staging.
  - Критерий готовности: приложен smoke report с доменами и cookie attributes без секретов; reset email ведет на рабочую страницу; password reset/change завершает остальные sessions по принятой policy.

- [ ] `P0-SEC-004` Проверять реальные deployment secrets, а не только self-test scanner.
  - Факт: CI запускает `check-production-secrets --self-test`, что проверяет код guard, но не конфигурацию staging/production.
  - Риск: `change-me`, `guest/guest`, `root/1234`, пустой webhook secret или повторно используемый service token могут попасть в deployment, несмотря на зеленый CI.
  - Где смотреть: backend/bot secret scripts, release workflow, secret manager/deployment platform.
  - План: pre-deploy job получает только безопасные derived checks или ephemeral mounted env, валидирует entropy/denylist/required keys; не печатает значения; включает rotation metadata.
  - Критерий готовности: deployment с placeholder/pустым required secret автоматически отклоняется; есть тестовый failed deployment и документированная ротация bot/backend credential.

## P1 - Backend и данные

- [ ] `P1-BE-001` Усилить DB invariants и обработку конкурентных записей.
  - Факт: rating/listing enums, currency, price/rating ranges не полностью закреплены CHECK constraints. Duplicate email и duplicate review проверяются до insert, что оставляет race до unique violation.
  - Риск: невалидные состояния из CLI/admin/race; unique violation превращается в 500 вместо 409/422.
  - Где смотреть: users/listings/reviews migrations, signup и review services.
  - План: DB CHECK/enum-compatible constraints, catch named unique violations, idempotency keys для create actions, concurrency tests.
  - Критерий готовности: invalid direct insert отклоняется DB; параллельные signup/review возвращают предсказуемый API response без 500.

- [ ] `P1-BE-002` Расширить lifecycle объявления для реального marketplace.
  - Факт: statuses ограничены draft/pending/published/rejected/archived; нет reserved/sold/completed/paused/expired. Owner может редактировать published listing без обязательной повторной модерации; delete hard-удаляет историю.
  - Риск: нельзя корректно завершить одноразовую продажу, повторяемую услугу и eligibility отзыва; существенное изменение обходится без модерации.
  - План: отделить publication status от availability/transaction status либо явно расширить state machine; significant fields возвращают listing в pending; использовать soft delete/retention policy.
  - Критерий готовности: transition matrix и tests покрывают товар/услугу, owner/admin permissions, re-moderation и восстановление истории.

- [ ] `P1-BE-003` Сделать notifications надежным side effect через outbox.
  - Факт: DB channel использует `notifyNow`, email queue; moderation сначала сохраняет status и затем dispatch; login notification failure потенциально влияет на sign-in; единой atomic delivery policy нет.
  - Риск: состояние изменено, а обязательное уведомление потеряно, либо временный mail/DB сбой ломает core action.
  - План: transactional outbox event в той же DB transaction, idempotent consumer, channel delivery attempts, retry/backoff, permanent failure state. Security-critical уведомления не должны зависеть от marketing preference.
  - Критерий готовности: fault tests доказывают eventual delivery без дублей; admin видит failed/lag; moderation/sign-in не теряют event.

- [ ] `P1-BE-004` Нормализовать notification preferences и HTTP слой.
  - Факт: enum содержит messages/replies/views/recommendations/price/expiration/digest, для которых нет producers; multi-action controllers и requests находятся в общих файлах; update preferences не делает batch transaction.
  - Риск: UI обещает несуществующие события, архитектурные правила проекта нарушаются, частичное обновление оставляет неоднозначное состояние.
  - План: только реализованные event types доступны клиенту; отдельные invokable controllers/requests/use cases; transactional upsert; обязательные security events выделены отдельно.
  - Критерий готовности: contract test сравнивает backend supported preferences с frontend; partial failure не сохраняет часть настроек.

- [ ] `P1-BE-005` Масштабировать notifications storage/read operations.
  - Факт: `markAllRead` материализует unread collection; retention/index strategy для пользовательских уведомлений не зафиксирована.
  - План: query-level bulk update, indexes `(notifiable_type, notifiable_id, read_at, created_at)`, retention/archive job и query count tests.
  - Критерий готовности: операция на 10 000 уведомлений не загружает модели, укладывается в установленный budget и не блокирует list.

- [ ] `P1-BE-006` Переделать profile data export в безопасный асинхронный workflow.
  - Факт: HTTP синхронно формирует и отправляет JSON attachment; export включает только часть профиля/контактов/адресов и не включает listings/media/favorites/reviews/notifications/preferences/sessions/consents.
  - Риск: долгий request, чувствительный attachment в почтовых ящиках и ложное обещание «полная информация».
  - План: явно определить scope; queued snapshot с manifest/version; зашифрованный artifact в private storage; одноразовая expiring signed download после re-auth; status endpoint и audit event.
  - Критерий готовности: export completeness test, expiration/revocation, отсутствие файла в public storage/email attachment, понятный пользовательский статус.

- [ ] `P1-BE-007` Усилить session и credential security.
  - Факт: password change/reset не завершает остальные sessions; session list использует самописный UA parser и приблизительное `По IP` location; expired sessions могут отображаться.
  - План: revoke other sessions and remember tokens по policy; фильтровать expired; использовать поддерживаемый UA parser; geo-IP только с consent, лицензией БД и явной точностью; уведомление о новом входе считать обязательным security event.
  - Критерий готовности: tests на session fixation/revocation/expiry, письмо и UI одинаково отображают нормализованные device/browser/time/IP/location.

- [ ] `P1-BE-008` Убрать фиктивные персональные имена из регистрации.
  - Факт: упрощенная регистрация сохраняет sentinel `User`/`Account`, потому что domain/DB требуют non-null first/last name.
  - Риск: выдуманные данные попадают в письма, аудит, экспорт и публичное представление как реальные персональные данные.
  - План: сделать имена nullable до заполнения профиля либо ввести отдельный `display_name`/profile completeness; не подменять отсутствие значением.
  - Критерий готовности: новый пользователь без имени корректно отображается во всех DTO/mail/admin; миграция и tests не создают fake values.

- [ ] `P1-BE-009` Ввести verification/abuse policy для marketplace actions.
  - Факт: email verification не обязательна для создания объявления/отзыва; public catalog/location/news/reviews и часть authenticated mutations не имеют специализированных rate limits.
  - План: risk-based throttles по user/IP/action, verified email для trust-sensitive actions, cooldowns, structured abuse events; не блокировать поисковых роботов общим агрессивным лимитом.
  - Критерий готовности: documented limits и tests на 429/reset window; high-risk action недоступно неподтвержденному аккаунту.

- [ ] `P1-BE-010` Доставлять media conversions через API, а не оригиналы.
  - Факт: conversions card/gallery существуют, но listing payload в основном ориентируется на original URL; avatar допускает SVG; filesystem `throw/report` policy может скрывать ошибки.
  - Риск: лишний трафик/LCP, SVG attack surface, тихие storage failures.
  - План: versioned media DTO с original/card/gallery/width/height/placeholder; запретить или sanitize SVG; production storage должен выбрасывать и логировать write failures.
  - Критерий готовности: frontend использует нужную conversion; API tests и browser network assertions подтверждают размеры; SVG policy покрыта security test.

- [ ] `P1-BE-011` Сделать импорт категорий воспроизводимым и безопасным.
  - Факт: importer не использует параметр source последовательно; parser зависит от DOM Prom.ua; сопоставление parent/name создает дубли при rename/move; удаленные элементы не деактивируются; arbitrary URL повышает SSRF-риск.
  - План: stable external IDs, source/version/checksum/import manifest, preview diff/approval/rollback, allowlist hosts, deactivate missing, fixtures contract tests; подтвердить право использования источника.
  - Критерий готовности: повторный import идемпотентен; rename/move не дублирует; diff показывает create/update/deactivate; network parser проверяется fixture без обращения к сайту.

- [ ] `P1-BE-012` Оптимизировать импорт локаций без потери контроля.
  - Факт: Russia importer загружает большие JSON целиком, выполняет per-row save в большой transaction и многократно инвалидирует cache; удаленные source records остаются active.
  - План: stream/chunk parse, staging table и batch upsert, один cache version bump after commit, source manifest/checksum, deactivate policy и preview stats.
  - Критерий готовности: импорт полного набора укладывается в memory/time budget, повторяем, прерывание не оставляет partial state, reference tests проходят.

- [ ] `P1-BE-013` Оптимизировать public listing/location queries на реальных данных.
  - Факт: `%term% ILIKE`, JSON snapshot filters и category descendants требуют EXPLAIN; pagination sort не везде имеет уникальный tie-breaker; popular sort использует счетчик, который не инкрементируется.
  - План: seed/anonymized scale dataset, `EXPLAIN (ANALYZE, BUFFERS)`, pg_trgm/partial/composite indexes по доказанным запросам, stable `id` tie-breaker, view events с anti-fraud aggregation.
  - Критерий готовности: сохранены планы запросов и p95 budget; indexes используются; popular sort подтвержден тестом и не накручивается простым refresh.

- [ ] `P1-BE-014` Исправить cache invalidation и category tree edge cases.
  - Факт: version bump реализован через неатомарные get/set; hierarchy updates создают N+1/bump per save; branch ограничен глубиной; inactive root может протащить active descendants.
  - План: atomic increment/lock, transaction-level single invalidation, recursive DTO или documented depth, only-active на всем ancestor chain.
  - Критерий готовности: concurrent invalidation test, arbitrary depth fixture, query budget, inactive ancestor никогда не появляется в public tree.

- [ ] `P1-BE-015` Покрыть service API bot контрактами и scoped credentials.
  - Факт: статический bearer token дает доступ ко всем bot service endpoints; rotation/version/scopes отсутствуют; backend feature tests для Bot module не найдены; health проверяет controller reachability, а stats выполняет пять count queries.
  - План: hashed credential records или managed secret с key ID/scopes/rotation overlap; rate limit/audit; dependency-aware readiness; feature contract tests.
  - Критерий готовности: revoked/expired/wrong-scope credential отклоняется; token не логируется; health/stats success/degraded/auth cases покрыты.

## P1 - Frontend

- [ ] `P1-FE-001` Перенести публичную витрину на server-first rendering.
  - Факт: home/listing details получают основное content через client `useEffect`; сервер отдает skeleton, затем hydration запускает API.
  - Риск: слабые SEO/link previews, задержка LCP, пустой initial HTML и лишний waterfall.
  - План: public list/detail/category data загружать в Server Components через server API client и cache/revalidate; client hydrates filters/favorite state; metadata строить из public DTO.
  - Критерий готовности: HTML без JS содержит title/price/content; generated metadata/OG работают; LCP/TTFB/API budget соблюден; auth favorite state не раскрывается сервером чужому пользователю.

- [ ] `P1-FE-002` Реализовать стабильный публичный профиль продавца.
  - Факт: seller page является placeholder; для public listing нет stable seller ID, поэтому frontend строит slug из имени, что допускает collision и rename break.
  - План: backend public seller DTO с immutable public ID или unique slug, privacy fields allowlist, listings/reviews pagination; route `/sellers/{publicIdOrSlug}`.
  - Критерий готовности: два одинаковых имени не конфликтуют; rename сохраняет redirect; contact/privacy policy и share links покрыты E2E.

- [ ] `P1-FE-003` Исправить hydration strategy вместо глобального подавления предупреждений.
  - Факт: `suppressHydrationWarning` стоит на `<html>` и `<body>` и скрывает расхождения всего дерева.
  - План: определить источник theme mismatch, применить suppression только к атрибуту theme root либо server cookie/init script; добавить hydration regression test.
  - Критерий готовности: global suppression удален, console E2E не содержит hydration/script warnings в light/dark/system modes.

- [ ] `P1-FE-004` Усилить CSP и media origin policy.
  - Факт: production `script-src` содержит `'unsafe-inline'`; img/media разрешают весь `https:` несмотря на более узкие `remotePatterns` Next.
  - План: nonce/hash strategy с учетом актуальной документации Next 16; разрешить только фактические CDN/API origins; добавить `report-to` после настройки endpoint.
  - Критерий готовности: production smoke без CSP violations, inline injection не исполняется, unit tests проверяют allowlist и отсутствие broad wildcard.

- [ ] `P1-FE-005` Восстановить Feature-Sliced boundaries.
  - Факт: `src/shared/ui/header` импортирует entities/features, а shared Axios импортирует auth feature events. Это направленные зависимости снизу вверх.
  - План: перенести header в `widgets/header`; auth session events в shared-level contract или app provider; добавить ESLint restricted imports/FSD boundary plugin после оценки совместимости.
  - Критерий готовности: автоматическое правило запрещает shared -> entities/features/screens/widgets; существующие нарушения устранены без circular imports.

- [ ] `P1-FE-006` Сократить лишние Client Component boundaries и неправильный suffix `Action`.
  - Факт: найдено 68 файлов с `"use client"` и сотни props вида `onRetryAction`, `onChangeAction`, хотя это обычные callbacks, а не Next Server Actions.
  - Риск: увеличенный client graph, путаница архитектурного смысла и шумные API компонентов.
  - План: client boundary только у state/effect/browser providers; leaf presentation остается server-compatible; обычные callbacks называются `onRetry`, `onChange`, `onOpenChange`; `Action` сохраняется только для реальных server actions, если они появятся.
  - Критерий готовности: зафиксирован baseline уменьшения client modules/JS; lint/build/tests green; naming rule отражен в AGENTS.

- [ ] `P1-FE-007` Ввести единую server-state strategy.
  - Факт: category/location используют дублирующиеся Zustand TTL caches; screens повторяют `useEffect/isMounted/loading/error/pagination`; retry иногда делает `window.location.reload()`.
  - План: public cache - Next `fetch`/RSC; private reactive server state - TanStack Query с query keys/invalidation/retry policy; Zustand оставить для UI/client-only state.
  - Критерий готовности: ADR и pilot на notifications/favorites; нет дублирующего fetch при remount; mutation инвалидирует только нужные queries; hard reload retry удален.

- [ ] `P1-FE-008` Оптимизировать session bootstrap и notification polling.
  - Факт: global provider вызывает `/me` и на anonymous public page; authenticated header опрашивает notifications каждые 30 секунд даже при скрытой вкладке; часть async handlers может дать unhandled rejection.
  - План: server-known/session hint или lazy auth bootstrap без раскрытия; polling только authenticated+visible, backoff и refresh on open/focus; centralized mutation error handling.
  - Критерий готовности: anonymous first view не вызывает `/me`; hidden tab не poll; offline/500 не создает unhandled promise; request budget E2E соблюден.

- [ ] `P1-FE-009` Исправить auth form semantics и доступность.
  - Факт: формы используют `autocomplete="off"`, email местами не имеет корректного input type/autocomplete; это мешает password managers и assistive technology.
  - План: `email`/`username`, `current-password`, `new-password`, `one-time-code`; labels/descriptions/errors связать ARIA; focus management dialogs.
  - Критерий готовности: axe critical violations отсутствуют; keyboard-only auth/profile flows проходят; password manager semantics проверены.

- [ ] `P1-FE-010` Добавить route-level loading/error/metadata/robots/sitemap.
  - Факт: в App Router найден только `not-found.tsx`; нет `error.tsx`, `loading.tsx`, `robots.ts`, `sitemap.ts`, page metadata/generateMetadata для основных сущностей.
  - План: segment boundaries, user-safe error UI с telemetry correlation ID, dynamic listing/blog/seller metadata, canonical URLs, sitemap pagination/index policy.
  - Критерий готовности: 404 отличается от backend outage; crawler endpoints валидны; listing share preview содержит реальный title/image; E2E покрывает error boundary.

- [ ] `P1-FE-011` Не маскировать backend outage пустым блогом/404.
  - Факт: blog fetch catches broad errors и может трактовать outage как empty list или not found; Axios server request не использует Next cache, страницы принудительно dynamic.
  - План: различать 404/401/5xx/network/schema; use server fetch/revalidate tags; показывать retryable service error.
  - Критерий готовности: contract tests на 404 и 500; cache invalidation documented; outage не индексируется как настоящий 404.

- [ ] `P1-FE-012` Автоматизировать backend/frontend contract drift.
  - Факт: Scramble и Zod существуют отдельно; документация DTO уже расходится с кодом.
  - План: экспорт OpenAPI artifact backend, generate/validate selected API schemas или consumer-driven contract snapshots; не заменять domain adapters сырыми generated types.
  - Критерий готовности: несовместимое удаление/rename поля ломает CI обоих контрактов; public/private listing privacy assertions обязательны.

- [ ] `P1-FE-013` Расширить E2E и accessibility matrix.
  - Факт: 31 E2E проходит только в Desktop Chromium и в основном на API mocks.
  - План: critical suite на mobile Chromium + Firefox/WebKit; axe smoke; keyboard flows; отдельный небольшой staging smoke с реальным backend.
  - Критерий готовности: sign-in, public listings/filter, create listing/media, notifications, session/logout проходят на выбранной matrix; flaky retries не скрывают постоянную ошибку.

## P1 - Telegram bot

- [ ] `P1-BOT-001` Добавить timeout, retry и schema error policy backend client.
  - Факт: `aiohttp.ClientSession` создается без явного `ClientTimeout`; handlers ловят в основном `ClientError`, но JSON/Pydantic errors могут выйти наружу.
  - План: connect/read/total timeouts, bounded retry только idempotent GET с jitter, correlation/request ID, typed errors для 401/403/429/5xx/invalid payload.
  - Критерий готовности: async tests на timeout, disconnect, 5xx, invalid JSON/schema и recovery; команда не висит и дает безопасное сообщение.

- [ ] `P1-BOT-002` Не терять Telegram updates при каждом рестарте.
  - Факт: polling и webhook используют `drop_pending_updates=True`; production restart выбрасывает накопленные updates.
  - План: configurable startup policy, default preserve в production; intentional drop только операторской командой/runbook; idempotent callbacks.
  - Критерий готовности: restart integration test сохраняет update; duplicate callback не повторяет mutation.

- [ ] `P1-BOT-003` Сделать webhook secret и readiness обязательными в production.
  - Факт: `SNABIX_BOT_WEBHOOK_SECRET` допускает пустое значение; bot не предоставляет отдельный health/readiness endpoint.
  - План: Pydantic environment validation, readiness проверяет Telegram setup/backend reachability без раскрытия token; deployment healthcheck.
  - Критерий готовности: production settings без secret не стартуют с понятной ошибкой; orchestrator отличает liveness от readiness.

- [ ] `P1-BOT-004` Довести test suite до реального поведения.
  - Факт: выполняется только `test_settings_parses_admin_ids`; client, access, handlers, callbacks, command sync, webhook/polling не покрыты.
  - План: unit tests AccessService/formatters/config; mocked aiohttp client; aiogram handler/callback tests admin/non-admin; startup/shutdown/webhook tests.
  - Критерий готовности: каждый admin command имеет success/forbidden/backend failure test; coverage threshold вводится после появления meaningful suite, а не ради процента.

- [ ] `P1-BOT-005` Связать admin authorization с backend identity и аудитом.
  - Факт: доступ определяется только списком Telegram IDs из env; глобальные команды регистрируются и видны всем, хотя обработчики проверяют доступ.
  - План: Telegram account linking/admin grant в backend, short-lived or signed service context, revoke без redeploy, audit actor/action; scope permissions по командам.
  - Критерий готовности: отозванный admin теряет доступ без рестарта; unauthorized user не видит admin command menu; backend audit содержит Telegram actor.

- [ ] `P1-BOT-006` Создать production image и graceful runtime.
  - План: non-root pinned image, locked deps, signal shutdown закрывает session/bot, webhook/polling режим выбирается env, structured stdout logs.
  - Критерий готовности: container smoke, readiness, graceful termination без потери update и documented rollback.

## P1 - CI/CD, эксплуатация и территория

- [ ] `P1-CI-001` Усилить CI supply-chain и полный quality gate.
  - Факт: Actions используют floating major tags; backend CI не запускает Scramble analysis, frontend CI не запускает production build; dependency audits отсутствуют.
  - План: pin actions по commit SHA с Renovate/Dependabot updates; backend `task check`; frontend lint/typecheck/test/build/E2E/audit; bot locked install/check/audit; SBOM artifacts.
  - Критерий готовности: branch protection требует все jobs; stale vulnerability или build failure блокирует merge; action updates автоматизированы.

- [ ] `P1-CI-002` Создать deploy pipeline со staging smoke и rollback.
  - План: build once/promote image, migration compatibility gate, health/readiness, smoke auth/listing/media/mail/bot, automatic rollback только для безопасных cases, manual approval production.
  - Критерий готовности: documented first successful staging deploy и simulated rollback; secrets не попадают в logs/artifacts.

- [ ] `P1-CI-003` Автоматизировать performance budget.
  - Существующий бюджет: staging TTFB <= 500 ms, mobile LCP <= 2.5 s, public first-load JS <= 250 KB gzip, API requests <= 4, backend list query count <= 12.
  - План: Lighthouse/Playwright trace и bundle analyzer artifact; backend query-count/performance test на fixture scale; trend не только single hard threshold.
  - Критерий готовности: CI/release report сохраняет метрики и блокирует необъясненное превышение; budget пересматривается осознанно.

- [ ] `P1-OPS-003` Ввести observability без доступа приложения к Docker socket.
  - Факт: Filament widget проверяет DB/Redis/cache/storage/migrations/env/resources и TCP RabbitMQ, но не знает uptime/restarts, worker heartbeat, queue lag, scheduler, mail, frontend или bot.
  - План: metrics от deployment platform/Prometheus exporters/cAdvisor, queue lag/failed jobs, scheduler heartbeat, structured logs и trace/correlation ID. Не монтировать Docker socket в PHP container.
  - Критерий готовности: dashboard и alerts показывают RED/USE signals, queue/mail/scheduler failure; admin UI отображает только sanitized operational summary.

- [ ] `P1-OPS-004` Принять ADR по RabbitMQ против Redis queue.
  - Факт: Redis уже обязателен для cache/session; RabbitMQ используется в основном для notification/email queue, но DLQ/routing/replay преимущества не реализованы.
  - Вариант A: упростить MVP до Redis queue и сократить один stateful service.
  - Вариант B: оставить RabbitMQ, добавить DLQ, retry/replay, queue topology, metrics и runbook.
  - Критерий готовности: ADR содержит нагрузку, guarantees, failure modes и стоимость эксплуатации; не поддерживаются две очереди без причины.

- [ ] `P1-PRODUCT-001` Утвердить территориальный и юридический scope запуска.
  - Факт: продукт, локации и язык ориентированы преимущественно на РФ, но нет одного ADR с территорией, оператором, возрастом, доставкой и правилами точного адреса.
  - План: страна/регионы/города/поселки, timezone/currency, карта/геокодер, видимость адреса, cross-region сделки, data residency и legal review.
  - Критерий готовности: product ADR и legal sign-off; UI/API не обещают неподдерживаемую географию; privacy/policies/cookies содержат реквизиты и реальные процессы.

## P2 - Архитектура, naming и сопровождаемость

- [ ] `P2-ARCH-001` Зафиксировать честную backend architecture boundary.
  - Факт: Domain contracts импортируют Eloquent models, Laravel collections/pagination; Review напрямую использует Eloquent service, тогда как другие модули используют ceremonial Input/Handler/Output.
  - Риск: формально «чистая» архитектура требует много классов, но не дает независимого domain core; разные модули развиваются по разным правилам.
  - Решение: выбрать один путь.
  - Рекомендуемый путь: pragmatic modular monolith, Eloquent разрешен в Application/Infrastructure, Domain содержит enum/policy/value objects без Laravel; DTO/use cases применяются там, где дают contract/transaction value.
  - Альтернатива: реальная ports-and-adapters boundary с domain entities/read DTO и Laravel-free interfaces, но стоимость сейчас выше пользы.
  - Критерий готовности: ADR, dependency rules и один refactored vertical slice; новые модули следуют выбранному пути.

- [ ] `P2-NAME-001` Нормализовать backend CLI class naming и namespaces.
  - Факт: `SharedCLICleanupStorage`, `CatalogCLIImportCategories`, `AuthCLIMakeAdminUser`, `MediaCLICleanupOrphanFiles` кодируют модуль и CLI в имени вместо namespace/роли.
  - План: `App\Shared\CLI\CleanupStorageCommand`, `App\Catalog\CLI\ImportCategoriesCommand`, `App\Auth\CLI\CreateAdminCommand`, `App\Media\CLI\CleanupOrphanFilesCommand`; Artisan signature остается стабильной.
  - Почему: имя отвечает «что делает класс», namespace отвечает «где он живет»; проще искать и читать DI.
  - Критерий готовности: команды зарегистрированы, docs/tests обновлены, старые class names отсутствуют.

- [ ] `P2-NAME-002` Уточнить review vocabulary и разнести обязанности.
  - Факт: `reviewee` технически корректно, но продукт говорит о продавце; `UserReviewService` одновременно валидирует eligibility, создает review и пересчитывает aggregate.
  - План: если отзывы только продавцу - `seller_id`/`subject_user_id`; `CreateUserReviewHandler`, `ReviewEligibilityPolicy`, `SellerRatingProjector`. Не переименовывать колонку без API migration plan.
  - Критерий готовности: ubiquitous language закреплен ADR/API docs; mapper/DB/API используют однозначные термины.

- [ ] `P2-NAME-003` Нормализовать frontend callback naming.
  - Факт: suffix `Action` используется для обычных React callbacks почти во всех UI-слоях.
  - План: `onOpenChange`, `onRetry`, `onSubmit`, `onFavoriteToggle`; зарезервировать Server Action naming для функций с соответствующей семантикой.
  - Критерий готовности: новые компоненты не добавляют fake Action props; mechanical migration идет по feature и не смешивается с behavior changes.

- [ ] `P2-NAME-004` Зафиксировать naming полей между DB/API/frontend.
  - Факт: DB использует snake_case, API camelCase, но встречаются semantic variants `fullname/fullName`, `about/description`, generic `type/status` и numeric enum values.
  - План: schema conventions: `...Id`, timestamp ISO 8601, money minor units/currency, public stable IDs, enum strings на API boundary; compatibility adapters для миграций.
  - Критерий готовности: conventions в API docs; contract tests не допускают случайные variants; rename имеет deprecation window.

- [ ] `P2-CODE-001` Декомпозировать крупные backend production-файлы по обязанностям.
  - Текущий baseline: `SharedCLICleanupStorage.php` 357, `NewsPostForm.php` 349, `ListingAttributeValueSynchronizer.php` 331, `CategoryAttributeDefinitionNormalizer.php` 325, `SystemHealthChecksWidget.php` 324.
  - План: command orchestration + services/reporting; Filament schema sections; attribute validation/dependency/persistence split; health check providers/view model.
  - Критерий готовности: каждый затронутый файл ниже установленной границы либо имеет отдельное датированное исключение с причиной; tests не ухудшены.

- [ ] `P2-CODE-002` Декомпозировать крупные frontend-файлы без дробления ради строк.
  - Текущий baseline: `about-page.tsx` 323, `settings-privacy-page.tsx` 322, `listings-page.tsx` 293, `settings-sessions-page.tsx` 279, category store 267, notifications menu 265, share profile 257.
  - План: отделять data/model, semantic sections и reusable UI; не создавать десятки одноразовых wrappers.
  - Критерий готовности: изменяемый legacy-файл не растет; extracted unit имеет собственную ответственность/test.

- [ ] `P2-CODE-003` Удалить dead/stale frontend code и зависимости.
  - Факт: `shared/lib/access-token.ts` не используется и противоречит Sanctum cookie flow; `mock-data.ts` содержит фальшивые метрики; `fallback-posts.ts` является тестовой fixture в production tree; `@fiddle-digital/string-tune` и `embla-carousel-react` не имеют найденного runtime usage, Framer Motion используется точечно.
  - План: подтвердить `rg`/bundle usage, удалить dead modules/deps; fixture переместить в test; простую theme animation заменить CSS только если bundle benefit измерим.
  - Критерий готовности: dependency graph/build green, bundle report фиксирует эффект, docs/legal copy не ссылается на access token localStorage.

- [ ] `P2-CODE-004` Удалить дубли и architectural leftovers backend.
  - Факт: найден дублирующий/мертвый `EloquentListingPolicy.php` рядом с фактическим `ListingPolicy.php`; generic scaffold descriptions в `composer.json`; некоторые docs обещают уже реализованные как future.
  - Критерий готовности: usage проверен, dead class удален, package metadata описывает Snabix, autoload/static checks проходят.

- [ ] `P2-CODE-005` Исправить ReferenceDataCache atomicity и invalidation ownership.
  - Факт: version key меняется через get+set, что теряет concurrent increments; model-level invalidation вызывает много bump при batch import.
  - План: atomic cache increment или namespace generation в DB/Redis; import transaction публикует один invalidation event.
  - Критерий готовности: concurrency test и импорт подтверждают ровно одно логическое обновление версии.

## P2 - UX, качество и эксплуатационные улучшения

- [ ] `P2-UX-001` Провести системный responsive/accessibility review marketplace UI.
  - Факт: есть крупные/nested card surfaces, неоднородные radii, отрицательный letter spacing и декоративные radial/orb classes; автоматической visual matrix нет.
  - План: токены typography/spacing/radius/focus, mobile 360/390, tablet, desktop; screenshots основных маршрутов; contrast/touch targets/overflow.
  - Критерий готовности: approved visual baseline, no overlap/overflow, WCAG AA для ключевых flows, изменения не превращают operational UI в landing page.

- [ ] `P2-UX-002` Разделить реализованные и будущие настройки пользователя.
  - Факт: account deactivation/delete UI не подключен; notification preferences перечисляют события без producers; seller profile placeholder доступен как route.
  - План: disabled с честным статусом только если это полезно либо скрывать до backend capability; capability contract вместо ручного рассинхрона.
  - Критерий готовности: пользователь не может нажать действие, которое только закрывает dialog без результата; E2E подтверждает каждую видимую команду.

- [ ] `P2-OPS-001` Ввести retention для notifications и business logs.
  - Факт: system logs имеют retention config/schedule; notifications retention отсутствует; удаление значимых событий может конфликтовать с audit/legal needs.
  - План: классификация technical/business/security, сроки, archive/anonymize/delete jobs, legal hold.
  - Критерий готовности: retention matrix, scheduled jobs, metrics и tests boundary dates.

- [ ] `P2-OPS-002` Улучшить Caddy/security/network defaults.
  - План: production TLS/headers/body limits/timeouts, trusted proxy handling, upload limits, internal-only dependencies, request IDs; local Caddy остается простым profile.
  - Критерий готовности: security header scan и upload/auth smoke на staging.

- [ ] `P2-DOC-001` Синхронизировать все документы из матрицы и установить владельцев.
  - План: закрывать строки матрицы по одной; architecture/API/release docs обновляются в том же PR, что behavior; `CHANGELOG.md` получает `Unreleased`.
  - Критерий готовности: каждая строка матрицы отмечена актуальной или удалена как дубликат; docs link check и terminology grep в CI.

- [ ] `P2-TEST-001` Устранить временное подавление typecheck dependency diagnostics.
  - Факт: `typecheck:full` фильтрует известные duplicate index signature diagnostics Radix вместо исправления dependency/type compatibility.
  - План: обновить совместимые Radix/TypeScript packages или upstream issue; allowlist имеет issue ID и expiry.
  - Критерий готовности: full `tsc --skipLibCheck false` проходит без фильтра либо исключение формально ограничено и датировано.

## P3 - Наращивание продукта

- [ ] `P3-DISCOVERY-001` Реализовать полнотекстовый поиск по объявлениям.
  - Этап 1: PostgreSQL FTS + `pg_trgm`, нормализованный search document, category/location/price filters, typo tolerance и rank; измерить EXPLAIN/p95.
  - Этап 2 только по метрикам: внешний search engine, если PostgreSQL не выдерживает объем, faceting/relevance или операционные требования.
  - Не начинать с Elasticsearch/OpenSearch без dataset и search quality metrics.
  - Критерий готовности: search relevance fixture, no-result analytics, abuse limits, latency budget и индекс freshness.

- [ ] `P3-DISCOVERY-002` Создать подборки и рекомендации от простого к сложному.
  - Этап 1: правила по category/location/price/new/popular, исключение own/archived/seen, diversity и freshness.
  - Этап 2: события impression/click/favorite/contact/hide, consent/retention, offline evaluation.
  - Этап 3: collaborative/content ranking только при достаточной выборке; обязательны объяснимость, cold start и opt-out.
  - Критерий готовности: baseline CTR/contact conversion, A/B guardrails, privacy review; нельзя называть random ordering персонализацией.

- [ ] `P3-INTERACTION-001` Ввести interaction/order layer.
  - Назначение: связать buyer, seller и listing; поддержать request/contact/booking/reservation/completion/cancel/dispute и review eligibility.
  - Для одноразового товара завершение закрывает availability; для услуги каждая выполненная interaction является отдельным основанием отзыва.
  - Критерий готовности: state machine, idempotency, snapshots, authorization, notifications и audit trail.

- [ ] `P3-TRUST-001` Добавить reports, moderation history и sanctions.
  - План: report listing/user/review, reason taxonomy, evidence, queue/SLA, moderator actions, warning/suspension/ban, appeal, immutable audit event.
  - Критерий готовности: permissions Shield, privacy-safe evidence, anti-abuse rate limits, moderation metrics.

- [ ] `P3-MESSAGING-001` Спроектировать безопасные диалоги покупатель-продавец.
  - Сначала решить, нужен ли in-platform chat или достаточно contact reveal/lead. Если chat нужен: spam controls, block/report, attachment scanning, retention, unread/read model и notification preferences.
  - Не добавлять WebSocket только ради online indicator; transport выбирается после требований.

- [ ] `P3-BOT-001` Реализовать добровольную привязку Telegram и каналы уведомлений.
  - План: short-lived one-time linking token, explicit consent, revoke, channel preference, delivery status; bot не получает пароль и не читает DB.
  - Критерий готовности: token replay невозможен, unlink прекращает delivery, security events остаются доступными на обязательном канале.

- [ ] `P3-MONETIZATION-001` Определить promoted listings только после корректного organic ranking.
  - Факт: `is_featured` уже влияет на сортировку, но billing/promotion lifecycle отсутствует.
  - План: campaign period, payment state, labeling, budget/refund, moderation, fairness и reporting.
  - Критерий готовности: featured нельзя включить произвольным API; пользователь видит маркировку; ranking test отделяет paid/organic.

- [ ] `P3-GEO-001` Решить карты, координаты и межрегиональные сценарии.
  - План: provider/license, precision, geocoding cache, private exact address, delivery radius, districts/settlements; PostGIS добавлять только при доказанной геофильтрации по расстоянию.
  - Критерий готовности: consent/privacy, fallback без карты и performance plan.

## Технологические решения

| Технология | Решение | Обоснование и условие пересмотра |
| --- | --- | --- |
| Laravel 12 + PHP 8.3 | Оставить | Подходит модульному монолиту; сначала обновить security patches и выровнять локальный runtime. |
| PostgreSQL | Оставить основным хранилищем | Достаточен для транзакций, FTS и trigram. PostGIS только для distance/geospatial query. |
| Redis | Оставить | Уже нужен cache/session; кандидат на упрощенную очередь после ADR. |
| RabbitMQ | Решить ADR | Сохранять только если реально используются DLQ/routing/replay и команда готова эксплуатировать отдельный broker. |
| Filament + Shield | Оставить и срочно обновить | Быстро дает админку и permissions; high advisory блокирует безопасный релиз текущей версии. |
| Spatie Media Library | Оставить | Проблема не в библиотеке, а в transactional orchestration и delivery contract проекта. |
| Next.js App Router | Оставить и обновить patch | Нужны RSC/server-first public pages, metadata и актуальная security patch version. |
| Axios | Оставить для browser Sanctum client | Для server rendering использовать server `fetch`/обертку с Next cache semantics. |
| Zustand | Оставить для client UI state | Не использовать как универсальный remote cache. |
| TanStack Query | Добавлять выборочно | Полезен для private mutable server state; не заменяет RSC/public cache. |
| PostgreSQL FTS/pg_trgm | Первый search engine | Ниже стоимость и консистентность; внешний engine только после benchmark/relevance limits. |
| Elasticsearch/OpenSearch | Пока не добавлять | Нет доказанного масштаба и search requirements, оправдывающих эксплуатационную стоимость. |
| Aiogram 3 | Оставить | Стек соответствует bot; нужны locked dependencies, timeout и тесты. |
| Python 3.11 или 3.12 | Стандартизировать | Python 3.9 локально расходится с CI и современным dependency ecosystem. |
| OpenTelemetry/Prometheus-compatible metrics | Добавить поэтапно | Нужны queue/scheduler/request signals; начать с correlation IDs, structured logs и ключевых метрик. |
| Микросервисы | Не вводить сейчас | Границы еще меняются, нагрузка не доказана; модульный монолит дешевле и надежнее. |
| Kubernetes | Не вводить по умолчанию | Сначала immutable images и один понятный deployment target; orchestrator выбирается требованиями эксплуатации. |

## Рекомендуемый порядок реализации

### Этап 0. Security и release blockers

1. `P0-SEC-001` - `P0-SEC-003`: обновить и зафиксировать зависимости.
2. `P0-PRIV-001`: закрыть утечку favorites DTO и добавить privacy tests.
3. `P0-LIST-001`: исправить публикацию/expiration.
4. `P0-DATA-001`: безопасный media replacement.
5. `P0-TRUST-001`: временно закрыть невалидный review creation или добавить eligibility.
6. `P0-AUTH-001`, `P0-SEC-004`: staging auth smoke и real secret guard.
7. `P0-OPS-001`, `P0-OPS-002`: production runtime, scheduler, backup/restore.

### Этап 1. Marketplace correctness

1. `P1-BE-001`, `P1-BE-002`: invariants и полный listing lifecycle.
2. `P3-INTERACTION-001`: минимальный interaction layer как основание отзывов.
3. `P1-BE-003` - `P1-BE-005`: outbox, preferences и storage notifications.
4. `P1-FE-002`: стабильный seller profile и reviews UX.
5. `P1-BE-009`: verified/abuse controls.

### Этап 2. Public experience и discovery

1. `P1-FE-001`, `P1-FE-010`, `P1-FE-011`: server-first, metadata, error semantics.
2. `P1-BE-013`, `P1-CI-003`: query и web performance budgets.
3. `P3-DISCOVERY-001`: PostgreSQL search.
4. `P3-DISCOVERY-002`: rule-based recommendations и event foundation.

### Этап 3. Сопровождаемость и эксплуатация

1. `P1-CI-001`, `P1-CI-002`: полный CI/deploy/rollback.
2. `P1-OPS-003`, `P1-OPS-004`: observability и queue ADR.
3. `P2-ARCH-001`, naming и file decomposition без смешивания с behavior changes.
4. `P2-DOC-001`: закрыть documentation matrix.

### Этап 4. Рост функций

1. Trust/safety reports и sanctions.
2. Telegram linking/notification channel.
3. Messaging только после решения interaction model.
4. Paid promotion только после прозрачного organic ranking.
5. Maps/PostGIS только после территориального ADR и требований расстояния.

## Правило закрытия задач

Для каждого выполненного пункта в этом файле нужно:

1. заменить `[ ]` или `[~]` на `[x]`;
2. добавить строку `Выполнено YYYY-MM-DD`;
3. перечислить коммиты backend/frontend/bot;
4. перечислить реальные проверки и их результат;
5. обновить связанный architecture/API/release документ;
6. если критерий изменился, объяснить это рядом, а не удалять неудобную часть истории.

Этот файл является планом и техническим доказательством, но не заменяет issue tracker. Для крупных пунктов создается отдельная issue/ADR, а ее ID добавляется к соответствующему checklist item.
