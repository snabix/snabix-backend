# Backend Audit

Дата аудита: 2026-05-24

Контекст: Snabix backend - Laravel 12 API и Filament admin panel для регионального marketplace товаров и услуг. Текущая архитектура ближе к DDD / application-first: `Http -> Application -> Domain -> Infrastructure`, с отдельными bounded contexts `Auth`, `Catalog`, `Listing`, `Media`, `Location`, `Shared`.

## Проверка

- [x] Изучены `AGENTS.md`, `routes/api.php`, `bootstrap/providers.php`, `composer.json`, `Taskfile.yaml`.
- [x] Проверены основные bounded contexts: `Auth`, `Catalog`, `Listing`, `Media`, `Location`, `Shared`.
- [x] Проверены текущие API routes, request/response/use case паттерны, policies, Filament resources, events и tests.
- [x] Выполнен `task check`: PHP CS Fixer dry-run, PHPStan, Scramble, Laravel tests.
- [x] Результат тестов: `83 passed`, `427 assertions`.

## Реализовано

- [x] Auth API: регистрация, вход, выход, профиль, обновление профиля, адреса, аватар, email verification, resend verification, forgot/reset password, change password.
- [x] Session expiration contract для `401/419` стандартизирован.
- [x] Email verification и password reset используют queue/job подход.
- [x] Catalog API: root categories, branch, category attributes с metadata, dependency rules и schema version.
- [x] Характеристики категорий управляются через Filament, а frontend получает их через публичный endpoint формы объявления.
- [x] Location module: регионы, города, импорт из JSON, Filament resources.
- [x] Listing API: create, update, show, delete, list owned, list public, submit-for-review, upload media.
- [x] Listing statuses централизованы через `ListingStatusTransitionPolicy`.
- [x] Required category attributes проверяются в application service.
- [x] Listing input normalization вынесена из repository в `ListingInputNormalizer`.
- [x] Listing attribute sync вынесен в `ListingAttributeValueSynchronizer`.
- [x] Listing media upload подключен к единому media-хранилищу.
- [x] Public/private listing DTO разделены.
- [x] Media module: Spatie Media Library, типы, visibility, path generator, Filament resource, preview.
- [x] Filament Shield / Spatie Permission подключены для admin roles/permissions.
- [x] Filament admin panel имеет collapsible sidebar.
- [x] Business audit реализован через loggable events и listener.
- [x] HTTP activity logging вынесен в middleware.
- [x] Health checks и system resources widgets реализованы.
- [x] Backend tests используют отдельную `snabix_test` database.

## Архитектура

### Сильные стороны

- [x] Контроллеры в основном тонкие: принимают `Request`, собирают `Input`, вызывают `Handler`, возвращают `Response`.
- [x] Use cases читаемые и хорошо отделены от HTTP слоя.
- [x] Domain слой содержит enums, events, contracts, value objects, policies и status policy.
- [x] Eloquent скрыт за repositories/services там, где это уже было критично для развития.
- [x] API response classes единообразны и совместимы со Scramble.
- [x] Tests покрывают auth, catalog, listings, media, locations, Filament permissions, docs.

### Риски и нарушения

- [x] `App\Shared\Infrastructure\Providers\AppServiceProvider` все еще содержит bindings и policies разных модулей. Это работает, но модульные responsibilities размыты.
- [x] Admin category attribute API защищен `auth:admin`, но use cases не вызывают `Gate::authorize()` на уровне application flow.
- [x] `ListCategoriesController` и use case существуют, но route не подключен. Сейчас это dead code рядом с активным `ListRootCategories`.
- [ ] `Listing update` не публикует audit event. Для marketplace важно логировать изменения цены, категории, статуса, описания и значимых полей.
- [x] Public listing API имеет пагинацию, но не имеет базовых фильтров `category/type/price/location/sort`.
- [ ] Нет admin moderation domain service для `publish/reject/archive/return-to-draft` с reason, actor и audit event.
- [ ] Media частично подключена к listing flow: есть upload, но нет delete/reorder/main image и audit events по media changes.
- [ ] Listing aggregate пока хранит media через generic media relation, но business rules изображений объявления еще не централизованы.
- [ ] DTO mapping есть, но нет contract tests, которые автоматически сравнивают backend DTO expectations с frontend adapters.
- [ ] Dependency rules категорий сохраняются и отдаются, но не применяются в backend validation.
- [ ] `EloquentCategoryRepository` содержит много business normalization и hierarchy logic; постепенно стоит выделить normalizer/domain service для slug/parent/hierarchy.
- [ ] `EloquentCategoryAttributeDefinitionRepository` содержит normalization для options/default/dependency rules; часть логики можно вынести в dedicated normalizer.
- [ ] Нет production security review по CORS/Sanctum/session/cookie/domain настройкам.
- [ ] Нет performance review индексов под реальные сценарии marketplace.

## API Матрица

### Auth

- [x] `POST /api/v1/auth/sign-up`
- [x] `POST /api/v1/auth/sign-in`
- [x] `POST /api/v1/auth/logout`
- [x] `GET /api/v1/auth/me`
- [x] `PATCH /api/v1/auth/me`
- [x] `GET /api/v1/auth/me/addresses`
- [x] `PUT /api/v1/auth/me/addresses`
- [x] `DELETE /api/v1/auth/me/addresses/{addressId}`
- [x] `POST /api/v1/auth/me/avatar`
- [x] `DELETE /api/v1/auth/me/avatar`
- [x] `POST /api/v1/auth/forgot-password`
- [x] `POST /api/v1/auth/reset-password`
- [x] `POST /api/v1/auth/change-password`
- [x] `POST /api/v1/auth/verify-email`
- [x] `POST /api/v1/auth/email-verification-notification`

Задачи:
- [ ] Документировать session lifetime, idle expiration, remember-me, logout-all-devices стратегию.
- [ ] Добавить audit event для reset password success/fail, если security-аудит требует эти события.
- [ ] Добавить tests на rate limit/cooldown headers.

### Catalog

- [x] `GET /api/v1/categories/list`
- [x] `GET /api/v1/categories/{categoryId}/branch`
- [x] `GET /api/v1/categories/{categoryId}/attributes`
- [x] Admin category attribute HTTP API отключен: управление характеристиками остается в Filament.

Задачи:
- [x] Решить судьбу `ListCategoriesController`: подключить осознанно как full tree/list endpoint или удалить.
- [ ] Применить `dependency_rules` в backend validation.

### Listings

- [x] `GET /api/v1/public/listings`
- [x] `GET /api/v1/listings`
- [x] `POST /api/v1/listings`
- [x] `GET /api/v1/listings/{listingId}`
- [x] `PATCH /api/v1/listings/{listingId}`
- [x] `POST /api/v1/listings/{listingId}/submit-for-review`
- [x] `POST /api/v1/listings/{listingId}/media`
- [x] `DELETE /api/v1/listings/{listingId}`

Задачи:
- [x] Добавить public filters: category, type, minPrice, maxPrice, sort.
- [ ] Добавить delete/reorder/set-main для listing media.
- [ ] Добавить audit events для update и media changes.
- [ ] Добавить admin moderation actions: publish, reject, archive, return-to-draft.
- [ ] Добавить domain rule повторной отправки отклоненного объявления на модерацию после редактирования.
- [x] Проверить индексы под `status`, `category_id`, `type`, `price`, `published_at`.

### Media

- [x] Единое media-хранилище.
- [x] Filament media resource с preview.
- [x] Upload для listing images.

Задачи:
- [ ] Добавить image conversions/thumbnails для карточек.
- [ ] Добавить orphan cleanup для непривязанных media.
- [ ] Добавить access-control для private media.
- [ ] Добавить media audit events.

### Admin / Filament

- [x] Shield permissions подключены.
- [x] Super admin bypass работает.
- [x] Ресурсы пользователей, администраторов, категорий, характеристик, объявлений, медиа, регионов и городов есть.
- [x] Collapsible sidebar включен.

Задачи:
- [x] Перенести модульные policies/bindings из shared `AppServiceProvider` в модульные providers.
- [ ] Добавить role seeders: `super_admin`, `moderator`, `content_manager`, `support`.
- [ ] Добавить moderation actions прямо в Filament tables/dashboard.
- [ ] Добавить tests Filament moderation actions.

## Уязвимые Места

- [ ] Admin API permissions: один `auth:admin` недостаточен для разных ролей админки.
- [ ] CORS/Sanctum/session cookies требуют отдельного production review перед публичным запуском.
- [ ] File upload: есть лимит 3MB и image validation для listing, но нет централизованной policy по назначению файла и расширениям для всех upload-сценариев.
- [ ] Public listings могут стать дорогими без фильтров, индексов и query review на реальных данных.
- [ ] Business audit неполный для изменения объявлений и модерации.

## План Задач

1. [x] Разгрузить `AppServiceProvider`: перенести auth/catalog/media/shared policies и bindings в модульные providers.
2. [x] Отключить admin category attribute HTTP API и оставить управление характеристиками через Filament.
3. [x] Решить dead code `ListCategoriesController`: подключить full catalog endpoint или удалить.
4. [ ] Добавить audit event для listing update.
5. [x] Реализовать public listing filters без полнотекстового поиска.
6. [ ] Реализовать listing media management: delete, reorder, set main image.
7. [ ] Реализовать admin moderation domain service и endpoints/actions.
8. [ ] Применить category dependency rules в backend validation.
9. [ ] Добавить backend/frontend DTO contract tests.
10. [ ] Провести production security review CORS/Sanctum/session/uploads.

## Вывод

Backend находится в хорошем состоянии для активной разработки: тесты зеленые, DDD-границы в целом соблюдаются, основные marketplace-модули уже связаны. Главный риск сейчас не в работоспособности, а в неполной зрелости marketplace-core: фильтры, модерация, media management, audit trail и admin permissions нужно закрыть до публичного запуска.
