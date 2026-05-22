# Backend Audit

Дата повторного аудита: 2026-05-20

Контекст продукта: Snabix - региональная marketplace-платформа для размещения товаров и услуг пользователей. Backend должен устойчиво закрывать авторизацию, каталог, объявления, медиа, модерацию, аудит действий и административное управление.

## Проверка

- [x] Повторно просмотрены `AGENTS.md`, `routes/api.php`, bounded contexts `Auth`, `Catalog`, `Listing`, `Media`, `Shared`, `Mail`, `CLI`.
- [x] Повторно проверена DDD-граница: `Http` -> `Application` -> `Domain` -> `Infrastructure`.
- [x] Проверены публичные, пользовательские и admin API endpoints.
- [x] Проверены feature/unit tests, PHP CS Fixer, PHPStan, Scramble.
- [x] Проверены текущие правила ролей/permissions в Filament через Shield/Spatie Permission.
- [ ] Нужен отдельный production security review перед публичным запуском.
- [ ] Нужен performance review запросов объявлений, категорий и фильтров после появления реального объема данных.

## Результаты Команд

- `task check` прошел.
- PHP CS Fixer dry-run прошел без изменений.
- PHPStan прошел без ошибок.
- Scramble analysis прошел без ошибок.
- Laravel tests прошли: 74 теста, 374 assertions.

## API Матрица

### Auth

- [x] `POST /api/v1/auth/sign-up` - работает, покрыт тестами, создает пользователя и отправляет email verification job.
- [x] `POST /api/v1/auth/sign-in` - работает, покрыт happy/failed/inactive сценариями.
- [x] `POST /api/v1/auth/logout` - работает через `auth:sanctum`, покрыт тестом.
- [x] `GET /api/v1/auth/me` - работает, возвращает профиль текущего пользователя.
- [x] `PATCH /api/v1/auth/me` - работает, обновляет профиль и повторно сбрасывает email verification при смене email.
- [x] `POST /api/v1/auth/me/avatar` - работает, умеет replace старого файла.
- [x] `DELETE /api/v1/auth/me/avatar` - работает, удаляет аватар.
- [x] `POST /api/v1/auth/forgot-password` - работает, отправляет reset email.
- [x] `POST /api/v1/auth/reset-password` - работает, меняет пароль по токену.
- [x] `POST /api/v1/auth/change-password` - работает для авторизованного пользователя.
- [x] `POST /api/v1/auth/verify-email` - работает через код подтверждения.
- [x] `POST /api/v1/auth/email-verification-notification` - работает, имеет throttle и application cooldown.

Что добавить: унифицировать response examples для auth errors в документации, добавить тесты на rate-limit/cooldown headers, добавить явную политику session lifetime в env/config и документацию для frontend.

### Catalog

- [x] `GET /api/v1/categories/list` - возвращает root categories.
- [x] `GET /api/v1/categories/{categoryId}/branch` - возвращает ветку категории.
- [x] `GET /api/v1/categories/{categoryId}/attributes` - возвращает характеристики с metadata, dependency rules и schema version.
- [x] `GET /api/v1/admin/category-attribute-definitions` - защищен `auth:admin`, возвращает список характеристик.
- [x] `GET /api/v1/admin/category-attribute-definitions/export` - защищен `auth:admin`, экспортирует характеристики.
- [x] `POST /api/v1/admin/category-attribute-definitions/import` - защищен `auth:admin`, импортирует характеристики.
- [x] `POST /api/v1/admin/category-attribute-definitions` - защищен `auth:admin`, создает характеристику.
- [x] `GET /api/v1/admin/category-attribute-definitions/{attributeDefinitionId}` - защищен `auth:admin`, показывает характеристику.
- [x] `PATCH /api/v1/admin/category-attribute-definitions/{attributeDefinitionId}` - защищен `auth:admin`, обновляет характеристику.
- [x] `DELETE /api/v1/admin/category-attribute-definitions/{attributeDefinitionId}` - защищен `auth:admin`, блокирует удаление при наличии значений объявлений.

Что добавить: admin API сейчас опирается на `auth:admin`, но не вызывает `Gate::authorize()`/policy внутри application flow. Для marketplace это важно, потому что после появления ролей "модератор", "контент-менеджер", "support" одного факта admin-сессии будет мало.

### Listings

- [x] `GET /api/v1/public/listings` - публичный список опубликованных объявлений, без owner/contact private fields.
- [x] `GET /api/v1/listings` - список объявлений текущего пользователя с пагинацией и фильтрами.
- [x] `POST /api/v1/listings` - создание объявления, статус `pending_review` или `draft` через `saveAsDraft`.
- [x] `GET /api/v1/listings/{listingId}` - просмотр своего объявления.
- [x] `PATCH /api/v1/listings/{listingId}` - обновление своего объявления без изменения moderation fields.
- [x] `POST /api/v1/listings/{listingId}/submit-for-review` - отправка черновика на модерацию.
- [x] `DELETE /api/v1/listings/{listingId}` - удаление своего объявления с audit event.

Что добавить: admin moderation actions `publish/reject/archive`, media attachments для объявлений, search/filter/sort public API, индексы под category/status/type/price/published_at, отдельные DTO и tests для moderation API.

### Media

- [x] Единое хранение реализовано через Spatie Media Library.
- [x] Есть типизация `MediaType` и `MediaVisibility`.
- [x] Есть path generator и service для create/replace/delete.
- [x] Есть Filament preview и таблица медиа.
- [x] Есть тесты replace/delete/move.

Что добавить: привязку media к объявлениям, conversions/thumbnails для карточек, orphan cleanup, access-control для private media, политику загрузки разных типов файлов по назначению.

### Shared, Health, Audit

- [x] HTTP activity logging вынесен в middleware.
- [x] Business audit идет через loggable events и listener.
- [x] Health widgets и system resources check покрыты тестом.
- [x] RabbitMQ и Redis присутствуют в инфраструктурном контуре.

Что добавить: retention policy для system logs по типам, отдельные event tests для listing moderation events, health endpoint/виджет для queue lag и failed jobs.

## DDD И Чистота Кода

### Что Хорошо

- [x] Контроллеры тонкие и в основном только собирают input DTO, вызывают handler и возвращают response resource.
- [x] FormRequest используется для HTTP validation и вычисляемых входных данных.
- [x] Use cases читаемые, сценарные, без прямого смешивания с Laravel controller logic.
- [x] Domain слой содержит enums, events, contracts, value objects и policies для ключевых правил.
- [x] Репозитории спрятали Eloquent и транзакции за contracts.
- [x] Правила публикации и переходов статуса вынесены в domain services.
- [x] Синхронизация характеристик вынесена из repository в отдельный synchronizer.
- [x] Тесты идут на отдельной test database `snabix_test`.

### Что Рисково

- [ ] `EloquentListingRepository` все еще содержит много normalization/validation методов. Это допустимо как persistence normalization, но часть логики `resolveType/resolveCondition/resolvePrice/assertTypeMatchesCategory` лучше постепенно перенести в dedicated application/domain normalizers, чтобы repository отвечал только за persistence.
- [ ] Admin category attribute API не использует policy/Gate на уровне use case. Это следующий естественный шаг после подключения Shield.
- [ ] `ListCategoriesController` и часть catalog use case существуют, но маршрут в `routes/api.php` сейчас не подключен. Нужно либо подключить осознанно, либо удалить как dead code.
- [ ] Listing update не публикует audit event. Для marketplace важно логировать изменение цены, категории, статуса черновика и значимых полей.
- [ ] Публичный listing API пока не имеет фильтров по category/type/price/location. Поиск можно оставить на будущее, но базовые фильтры нужны раньше полнотекстового поиска.
- [ ] Нет moderation domain service для admin-решений `publish/reject/archive` с reason, actor и audit event.
- [ ] Media пока не является частью listing aggregate/application flow.
- [ ] DTO mapping есть, но нет отдельного набора contract tests, который сравнивает backend examples с frontend expectations.

## Auth

- [x] Основные auth flows рабочие и покрыты тестами.
- [x] Email verification переведен на код и queue job.
- [x] Password reset и change password разделены правильно.
- [x] 401/419 session-expiration contract стандартизирован.

Что добавить:

- [ ] Документировать cookie/Sanctum session policy: lifetime, idle expiration, remember-me, logout all devices.
- [ ] Добавить endpoint `logout-all-devices`, когда появятся активные сессии в личном кабинете.
- [ ] Добавить audit event для reset password success/fail, если нужно видеть критичные security-события.
- [ ] Добавить frontend/backend contract test для `me.avatar`, чтобы не ломать UI профиля.

## Catalog И Характеристики

- [x] Категории имеют дерево, branch endpoint и demo seeder.
- [x] Slug policy выбрана глобальная.
- [x] Характеристики имеют dependency rules и schema version.
- [x] Значения объявления сохраняют snapshot схемы.
- [x] Удаление характеристики защищено при наличии listing values.
- [x] Есть bulk import/export.

Что добавить:

- [ ] Реально применить `dependency_rules` в frontend форме и backend validation, иначе правило описано, но не управляет поведением формы.
- [ ] Добавить UI/endpoint для preview формы категории в admin.
- [ ] Добавить миграционную стратегию изменения схемы характеристик: clone version, deprecate old fields, backward compatibility.
- [ ] Добавить tests на import malformed payload и частичный import failure.

## Listings

- [x] Create/update/show/delete/list/submit-for-review реализованы.
- [x] Public/private DTO границы зафиксированы.
- [x] Пользователь не может управлять чужими объявлениями.
- [x] Required attributes проверяются в application service.
- [x] Status transitions централизованы.

Что добавить:

- [ ] Admin moderation flow: `publish`, `reject`, `archive`, `return-to-draft`.
- [ ] Listing media attachments: upload/order/delete/main image.
- [ ] Public filters без полнотекстового поиска: category, type, price range, status published, sort.
- [ ] Audit trail для update, moderation actions и media changes.
- [ ] Индексы и query review под `public/listings` и personal listings.
- [ ] Domain rule для повторной отправки на модерацию после редактирования отклоненного объявления.

## Filament

- [x] Filament ресурсы разнесены по bounded contexts.
- [x] Shield/Spatie permissions подключены.
- [x] Super admin bypass реализован через Gate.
- [x] Есть страница модерации объявлений и виджеты.
- [x] Таблицы получили фильтры.

Что добавить:

- [ ] Проверить API permissions отдельно от Filament permissions, потому что Filament policy и API route middleware не всегда закрывают один и тот же сценарий.
- [ ] Добавить moderation actions прямо в dashboard/table actions.
- [ ] Добавить seed roles: `super_admin`, `moderator`, `content_manager`, `support`.
- [ ] Добавить тесты Filament actions на publish/reject, когда появятся moderation actions.

## Тестирование

- [x] Unit и feature tests проходят.
- [x] Есть тесты auth, catalog, listing, media, Filament permissions, docs.
- [x] PHPStan и Scramble проходят.

Что добавить:

- [ ] Contract tests для frontend DTO: `items/meta`, enum labels, avatar shape, category attributes shape.
- [ ] Tests для admin moderation actions.
- [ ] Tests для dependency rules.
- [ ] Tests для listing media attachments.
- [ ] Tests для orphan media cleanup.
- [ ] Tests для role-specific admin permissions, не только super-admin и admin-without-permissions.

## Обновленный План

1. [ ] Ввести Gate/policy checks в admin category attribute API use cases.
2. [ ] Реализовать admin moderation flow для объявлений.
3. [ ] Подключить media attachments к объявлениям.
4. [ ] Реализовать public listing filters без полнотекстового поиска.
5. [ ] Применить dependency rules на backend validation и frontend UI.
6. [ ] Добавить role seeders для админки.
7. [ ] Добавить contract tests для backend/frontend DTO.
8. [ ] Добавить health/audit checks для queue lag и failed jobs.

## Вывод

Backend уже выглядит не как учебный Laravel CRUD, а как нормальный product backend с bounded contexts, use cases, событиями, тестами и отдельной админкой. Главный следующий риск не в работоспособности, а в том, что marketplace быстро потребует модерацию, медиа объявлений, фильтры и более строгие admin permissions. Эти зоны стоит закрывать следующими, не ломая текущую DDD-структуру.
