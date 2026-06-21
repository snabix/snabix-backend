# Технический аудит Snabix

Дата: 2026-06-18
Область проверки: `snabix-frontend`, `snabix-backend`
Формат: архитектурный и кодовый аудит с перечнем практических правок

## 1. Краткое резюме

Проект находится в хорошем техническом состоянии: frontend собирается, lint проходит, backend имеет рабочую модульную структуру, статический анализ и тесты зеленые. Основная архитектурная идея уже читается:

- Frontend построен близко к Feature-Sliced Design: `entities`, `features`, `screens`, `widgets`, `shared`.
- Backend построен по доменным модулям: `Auth`, `Catalog`, `Listing`, `Location`, `Media`, `News`, `Shared`.
- Backend хорошо разделяет `Http`, `Application`, `Domain`, `Infrastructure`, `Filament`.
- Есть runtime-валидация API-ответов на frontend через `zod`.
- Есть PHPStan, PHP CS Fixer, Scramble, feature/unit tests.

Главные зоны риска сейчас не в падающих тестах, а в росте сложности:

- Слишком крупные frontend-компоненты и хуки.
- Дублирование DTO/mapper-логики между публичными и приватными объявлениями.
- Ручное сопровождение API-контрактов между backend и frontend.
- Потенциальные N+1 запросы в сборке breadcrumbs категорий.
- Недостаточная формализация frontend-сессии и auth-cookie state.
- Недостаток e2e-тестов для ключевых пользовательских сценариев.

## 2. Проверки, которые были выполнены

### Frontend

- `npm run lint` - успешно.
- `npm run build` - успешно.
- `npm run test` - успешно: 21 test files, 69 tests.
- Проверены ключевые слои:
  - `src/shared/api`
  - `src/features/auth`
  - `src/features/listing`
  - `src/entities/listing`
  - `src/screens/home`
  - `src/screens/listings`
  - `src/screens/account`
  - `src/shared/ui/header`
  - `src/shared/ui/logo`

### Backend

- `task check` - успешно.
- `task test` - успешно: 99 tests, 515 assertions.
- `vendor/bin/phpstan analyse --memory-limit=1G` - успешно.
- `php-cs-fixer --dry-run` через `task check` - успешно.
- `php artisan scramble:analyze` через `task check` - успешно.
- Проверены ключевые слои:
  - `routes/api.php`
  - `app/Auth`
  - `app/Catalog`
  - `app/Listing`
  - `app/Location`
  - `app/Media`
  - `app/News`
  - `app/Shared`

## 3. Текущее состояние рабочих деревьев

### Frontend

На момент аудита frontend содержит незакоммиченные изменения:

- `package.json`
- `package-lock.json`
- `public/snabix.png`
- `src/screens/account/settings/ui/settings-pages.tsx`
- `src/shared/ui/header/Header.tsx`
- `src/shared/ui/logo.tsx`
- `src/shared/ui/shadcn/switch.tsx`

Важно: аудит не откатывал эти изменения и не менял их логику. В рамках аудита добавлен только этот markdown-файл в корне workspace.

### Backend

Backend рабочее дерево было чистым перед аудитом.

## 4. Размер и сложность кодовой базы

### Frontend

Всего TypeScript/TSX строк по `src`: около `19 484`.

Самые крупные файлы:

- `src/screens/account/settings/ui/settings-pages.tsx` - 1041 строка.
- `src/features/listing/ui/listing-form.tsx` - 840 строк.
- `src/features/listing/model/use-listing-form-state.ts` - 619 строк.
- `src/screens/account/listings/details/ui/listing-details-page.tsx` - 513 строк.
- `src/screens/account/profile/ui/profile-addresses-section.tsx` - 476 строк.
- `src/entities/listing/ui/listing-card.tsx` - 443 строки.
- `src/screens/listings/ui/public-listings-page.tsx` - 424 строки.
- `src/screens/blog/model/posts.ts` - 412 строк.

Вывод: frontend уже перешел границу, где часть файлов стала сложной для безопасного изменения. Приоритетный рефакторинг нужен не из-за багов, а из-за будущей скорости разработки.

### Backend

Всего PHP строк по `app`: около `24 477`.

Самые крупные файлы:

- `app/Listing/Application/Services/ListingInputNormalizer.php` - 353 строки.
- `app/Listing/Infrastructure/Repositories/EloquentListingRepository.php` - 345 строк.
- `app/Listing/Infrastructure/Services/ListingAttributeValueSynchronizer.php` - 331 строка.
- `app/Catalog/Application/Services/CategoryAttributeDefinitionNormalizer.php` - 325 строк.
- `app/Location/Application/Services/RussiaLocationImporter.php` - 272 строки.
- `app/Listing/Application/Services/ListingAddressSnapshotService.php` - 256 строк.
- `app/Listing/Application/Services/CategoryAttributeDependencyRuleEvaluator.php` - 216 строк.

Вывод: backend хорошо структурирован по модулям, но внутри модуля `Listing` появились классы-комбайны. Их нужно дробить раньше, чем логика модерации, продвижения, карт и алгоритмизации объявлений станет еще сложнее.

## 5. Архитектура frontend

### Что хорошо

- Слои `entities`, `features`, `screens`, `widgets`, `shared` читаются и в целом соблюдаются.
- API-вызовы вынесены из UI в `api`-модули.
- Сложная форма объявления уже разделена на model/ui/api, что лучше, чем хранить все в одном компоненте.
- Есть runtime-контракты через `zod`, что снижает риск несовпадения backend/frontend.
- Используются современные React/Next-паттерны: `useTransition`, `useEffectEvent`, server/client boundaries.

### Что нужно поправить

- [x] [P1] Разбить `src/screens/account/settings/ui/settings-pages.tsx`.
  - Сейчас файл содержит слишком много страниц/секций настроек.
  - Риск: любое изменение настроек будет конфликтным и трудно ревьюиться.
  - Предложение: разделить на `settings-profile-page.tsx`, `settings-account-page.tsx`, `settings-notifications-page.tsx`, `settings-addresses-page.tsx`, `settings-sessions-page.tsx`.
  - Выполнено 2026-06-19: страницы вынесены в отдельные модули, общий UI перенесен в `settings-shared.tsx`, а совместимость текущих route imports сохранена через `settings-pages.ts` barrel-export.

- [P1] Разбить `src/features/listing/ui/listing-form.tsx`.
  - Сейчас форма объединяет категории, атрибуты, адрес, медиа, submit state и UI.
  - Риск: добавление карт, автокомплита адресов, черновиков и предпросмотра сильно усложнит файл.
  - Предложение: выделить `ListingCategorySection`, `ListingAttributesSection`, `ListingPricingSection`, `ListingAddressSection`, `ListingMediaSection`, `ListingSubmitBar`.

- [P1] Разбить `src/features/listing/model/use-listing-form-state.ts`.
  - Сейчас hook управляет категориями, атрибутами, адресами, медиа, submit flow и retry upload.
  - Риск: состояние формы становится неявной бизнес-логикой frontend.
  - Предложение: выделить хуки:
    - `useListingCategoryState`
    - `useListingAttributeState`
    - `useListingAddressState`
    - `useListingMediaState`
    - `useListingSubmit`

- [x] [P1] Довести `src/shared/lib/auth-session.ts` до реальной логики.
  - Сейчас `shouldCheckCookieSession()` всегда возвращает `true`.
  - Сейчас `clearCookieSessionState()` пустой.
  - Риск: команда может думать, что есть полноценный cookie session state, хотя файл фактически no-op.
  - Предложение: либо реализовать cookie/local marker, либо переименовать/удалить слой как лишнюю абстракцию.
  - Выполнено 2026-06-19: no-op слой удален. `SessionProvider` теперь всегда восстанавливает HttpOnly Sanctum-сессию через `/auth/me`, а `401/419` обрабатываются централизованным unauthorized event без фиктивного локального маркера.

- [x] [P1] Унифицировать API-клиент и parsing.
  - В API-файлах часто повторяется `ApiDataResponse<unknown>` и касты через `as`.
  - Примеры:
    - `src/features/listing/api/list-public-listings.ts`
    - `src/features/listing/api/list-listings.ts`
    - `src/features/listing/api/show-listing.ts`
    - `src/entities/category/api/list-categories.ts`
    - `src/entities/news/api/list-news-posts.ts`
  - Риск: часть типов защищена `zod`, но TypeScript доверяет ручным cast.
  - Предложение: сделать универсальные helpers:
    - `getData(schema, url, config)`
    - `getPaginated(schema, url, config)`
    - `postData(schema, url, payload)`
  - Выполнено 2026-06-19: добавлен shared validation boundary `validated-request.ts` с `getData`, `getPaginated`, `postData`, `patchData`, `deleteData`; Listing, Category, News, User и active sessions API переведены на helpers. `ApiDataResponse<unknown>` локализован внутри shared helper, ручные casts удалены из entity/feature API.

- [x] [P1] Уменьшить использование `.passthrough()` в `src/shared/api/api-schemas.ts`.
  - Сейчас почти все schemas допускают лишние поля.
  - Это удобно при развитии API, но плохо ловит случайный drift.
  - Предложение: для стабильных DTO использовать strict-схемы, а `passthrough` оставлять только там, где backend реально расширяемый.
  - Выполнено 2026-06-20: стабильные user/session/category/listing/news DTO и вложенные структуры переведены на `.strict()`. Количество `.passthrough()` сокращено с 26 до 1; расширяемым оставлен только полиморфный `newsContentBlockSchema`. Добавлены regression tests на unknown fields и утечку приватного `userId` в публичный listing contract.

- [P2] Настроить `next.config.ts`.
  - Сейчас config пустой.
  - Нужно добавить:
    - [x] `images.remotePatterns` для backend media URLs. Выполнено 2026-06-20: разрешены только настроенный API-origin и редакционный `images.unsplash.com`, redirects отключены.
    - [x] security headers. Выполнено 2026-06-20: frontend-защита настроена в `next.config.ts`.
    - production cache policy для статичных ассетов.
    - возможные redirects/rewrites, если API origin отличается.

- [x] [P2] Заменить повторяющиеся `<img>` с `eslint-disable`.
  - Найдены отключения `@next/next/no-img-element` в listing media, blog, avatar, uploader.
  - Часть случаев оправдана для blob preview, но часть лучше закрыть `next/image`.
  - Предложение: создать `SafeImage` или `MediaImage`, где централизованно решать `blob`, external URL и Next Image.
  - Выполнено 2026-06-20: создан shared `MediaImage` поверх `next/image`, который автоматически отключает optimizer только для `blob:`/`data:`. Все 13 локальных suppressions удалены из listing gallery/uploader, avatar, category и blog UI; добавлены корректные `fill`, размеры и `sizes`, remote hosts ограничены в Next config. Добавлены regression tests.

- [x] [P2] Добавить e2e-тесты.
  - Сейчас frontend tests хорошие, но нет проверки full user flow.
  - Нужно покрыть:
    - sign in/sign up;
    - создание объявления;
    - загрузку медиа;
    - редактирование/архивирование;
    - избранное;
    - фильтрацию региона/города;
    - профильные адреса.
  - Выполнено 2026-06-20: добавлен Playwright Chromium и 6 stateful browser e2e-сценариев в `tests/e2e`. Покрыты sign in/sign up, создание объявления с media upload, редактирование и архивирование, добавление/список избранного, фильтрация региона/города и сохранение профильного адреса. Frontend e2e использует детерминированный API interception, backend-контракты продолжают проверяться Laravel feature tests.

- [x] [P2] Уточнить `tsconfig`.
  - `strict: true` включен, это хорошо.
  - Но `allowJs: true` и `skipLibCheck: true` снижают строгость.
  - Предложение: проверить необходимость `allowJs`; если JS-файлов нет, выключить.
  - Выполнено 2026-06-20: `allowJs` выключен после проверки отсутствия JS/JSX-исходников; добавлен отдельный `npm run typecheck`. `skipLibCheck` осознанно сохранен: строгий library check выявляет конфликты сгенерированных `.next/types`/`.next/dev/types` и duplicate index signatures Radix, не ошибки application-кода. Собственные TS/TSX и e2e-файлы полностью проверяются `tsc --noEmit`.

- [x] [P3] Ввести единый критерий максимального размера файла.
  - Например:
    - UI-компоненты: до 250-300 строк.
    - hooks: до 250 строк.
    - schema/contracts: до 300 строк.
  - Все, что больше, требует обоснования или декомпозиции.
  - Выполнено 2026-06-20: критерии закреплены в frontend `AGENTS.md`: UI 250/300 строк, hooks 250, schemas/contracts и остальные production TS/TSX 300, tests/fixtures целевой 300 и максимум 400. Новые превышения запрещены; существующие legacy-файлы нельзя увеличивать без декомпозиции либо явного обоснования и отдельной задачи.

## 6. Архитектура backend

### Что хорошо

- Модули backend разделены по bounded context.
- Для use cases есть отдельные `Input`, `Handler`, `Output`, `Request`, `Response`.
- Domain enums используются системно.
- Есть политики публикации и переходов статусов.
- Есть feature tests для ключевой бизнес-логики.
- Scramble документация успешно генерируется.
- PHPStan проходит без ошибок.

### Что нужно поправить

- [x] [P1] Разделить `EloquentListingRepository`.
  - Сейчас класс отвечает за:
    - owned list queries;
    - public list queries;
    - create;
    - update;
    - status transitions;
    - delete;
    - slug generation;
    - category filtering;
    - location search filtering.
  - Риск: repository становится God Object внутри Listing.
  - Предложение:
    - `ListingReadRepository`
    - `PublicListingQuery`
    - `OwnedListingQuery`
    - `ListingWriter`
    - `ListingSlugGenerator`
  - Выполнено 2026-06-20: God Object и широкий `ListingRepositoryInterface` удалены. Добавлены узкие контракты `ListingReadRepositoryInterface`, `OwnedListingQueryInterface`, `PublicListingQueryInterface`, `ListingWriterInterface` и отдельные реализации read/query/write/slug. Все Listing handlers переведены только на необходимые зависимости, DI bindings обновлены. Неиспользуемый `findOwnedByUser` удален. SQL-фильтры, eager loading, сортировки, транзакции, normalization и status policy сохранены без изменения поведения. Добавлен regression test на container composition и уникальность slug; `composer check`, Scramble и 101 backend test проходят.

- [P1] Убрать дублирование `ListingPayloadMapper` и `PublicListingPayloadMapper`.
  - Сейчас файлы почти полностью повторяют:
    - media mapping;
    - category mapping;
    - breadcrumbs;
    - location mapping;
    - attribute value mapping.
  - Риск: публичный mapper может случайно начать отдавать приватные поля или отстать от приватного mapper.
  - Предложение:
    - общий `ListingPayloadAssembler`;
    - политика видимости `private/public`;
    - отдельный allowlist публичных полей.

- [x] [P1] Исправить потенциальный N+1 в breadcrumbs категорий.
  - В mapper метод `categoryBreadcrumbs()` вызывает `$current->parentCategory()->first()` в цикле.
  - В публичном списке это может дать дополнительные запросы на каждое объявление и каждый уровень категории.
  - Предложение:
    - использовать precomputed `path/full_name`, если достаточно;
    - eager-load parent chain;
    - вынести построение breadcrumbs в `CategoryBreadcrumbService` с cache.
  - Выполнено 2026-06-21: добавлен request-scoped `CategoryBreadcrumbService`. Он одним запросом загружает компактный индекс категорий, строит `breadcrumbs/fullName` без SQL в цикле и кеширует trail по category ID до конца request/job scope. Private/public listing mappers переведены на сервис; вызовы `parentCategory()->first()` и query-based accessor `full_name` из listing mapping удалены. Query-count test на 5 объявлений и 3 уровня категорий подтверждает 1 общий запрос при первом mapping и 0 при повторном. PHPStan, Scramble и 102 backend test проходят.

- [P1] Разделить `ListingInputNormalizer`.
  - Сейчас он нормализует create и update, валидирует тип/категорию, приводит цену, контакты, адреса, moderation-поля.
  - Риск: пользовательские и админские сценарии будут пересекаться.
  - Предложение:
    - `ListingCreateNormalizer`
    - `ListingUpdateNormalizer`
    - `ListingModerationNormalizer`
    - value objects для price/currency/contact.

- [P1] Формализовать адресный snapshot.
  - Сейчас `ListingAddressSnapshotService` возвращает массив.
  - User address payload и listing address snapshot похожи, но живут отдельно.
  - Риск: структура адреса начнет расходиться при добавлении карт/координат/провайдеров.
  - Предложение:
    - `AddressSnapshotData`;
    - `LocationPayloadMapper`;
    - единый контракт для profile/custom/map адреса.

- [P1] Подготовить location search к росту данных.
  - Сейчас публичная фильтрация может искать по `region_id/city_id`, а также по `ilike` и JSON snapshot.
  - Для небольшого объема это нормально.
  - Для большого каталога JSON `ilike` станет дорогим.
  - Предложение:
    - предпочитать `regionId/cityId` на frontend;
    - добавить trigram index или отдельные searchable columns;
    - рассмотреть Meilisearch/Typesense для публичного поиска объявлений.

- [P2] Добавить throttle на write-heavy endpoints.
  - Сейчас throttle явно есть на auth endpoints.
  - Для listing/media/favorite endpoints стоит добавить отдельные лимиты.
  - Риск: favorite spam, media upload abuse, listing mutation spam.
  - Предложение:
    - `throttle:listings.write`;
    - `throttle:listings.media`;
    - `throttle:listings.favorite`.

- [x] [P2] Унифицировать response resources.
  - Почти каждый endpoint имеет однотипный `JsonResource`.
  - Это нормально для явности, но много boilerplate.
  - Предложение:
    - оставить явные классы для docs;
    - создать общий trait/base method для `toArray`.
  - Выполнено 2026-06-21: добавлены shared `OutputResource`, `ItemOutputResource` и `ItemsOutputResource` для полного DTO, одиночного `item` и коллекции `items`. 40 endpoint-specific response-классов сохранены для явных Scramble-контрактов и делегируют сериализацию shared-базам; нестандартный `VerifyEmailResponse` оставлен самостоятельным. Добавлены runtime/fail-fast unit tests и regression test OpenAPI schemas. PHPStan, Scramble и 108 backend tests проходят.

- [P2] Расширить тесты производительности запросов.
  - Сейчас feature tests проверяют поведение, но не количество SQL queries.
  - Нужно добавить query count tests для:
    - [x] public listing list;
    - [ ] account listing list;
    - [ ] favorites list;
    - [ ] listing detail;
    - [ ] category branch.

- [P2] Проверить lifecycle удаления listing media.
  - `delete()` в repository удаляет attribute values и listing.
  - Нужно явно подтвердить тестом, что связанные media-файлы и записи удаляются через Spatie/custom media lifecycle.

- [P3] Разделить большие Filament schema/table classes.
  - Filament-файлы крупные, но это менее критично, чем runtime API.
  - Все же при росте админки лучше дробить sections/actions/columns.

## 7. API и запросы

### Auth

Endpoints:

- `POST /api/v1/auth/sign-up`
- `POST /api/v1/auth/sign-in`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/verify-email`
- `POST /api/v1/auth/email-verification-notification`
- `GET /api/v1/auth/me`
- `PATCH /api/v1/auth/me`
- `GET /api/v1/auth/me/addresses`
- `PUT /api/v1/auth/me/addresses`
- `DELETE /api/v1/auth/me/addresses/{addressId}`
- `POST /api/v1/auth/change-password`
- `GET /api/v1/auth/sessions`
- `DELETE /api/v1/auth/sessions`
- `DELETE /api/v1/auth/sessions/{sessionId}`
- `POST /api/v1/auth/me/avatar`
- `DELETE /api/v1/auth/me/avatar`
- `POST /api/v1/auth/logout`

Замечания:

- [P1] Frontend session-state нужно сделать реальным или убрать no-op.
- [P2] Нужно добавить e2e-tests на auth/session expiration.
- [P2] Проверить, что frontend корректно обрабатывает `401` и `419` на всех приватных страницах.

### Catalog

Endpoints:

- `GET /api/v1/categories/list`
- `GET /api/v1/categories/{categoryId}/branch`
- `GET /api/v1/categories/{categoryId}/attributes`

Замечания:

- [P1] Breadcrumbs лучше отдавать из backend как готовую структуру без дополнительных запросов в listing mapper.
- [P2] Frontend category store стоит покрыть тестами cache invalidation и повторной загрузки веток.

### Locations

Endpoints:

- `GET /api/v1/locations/regions`
- `GET /api/v1/locations/cities`

Замечания:

- [P1] Для будущей карты нужно расширить контракт координатами, timezone, place id/provider.
- [P1] Добавить frontend autocomplete/select, который передает `regionId/cityId`, а не только текст.
- [P2] Добавить debounce и cancellation для поиска городов.

### News

Endpoints:

- `GET /api/v1/news`
- `GET /api/v1/news/{slug}`

Замечания:

- [P2] `src/screens/blog/model/posts.ts` содержит большой локальный контент/модель. Нужно отделить static fallback от runtime API adapter.
- [P2] Для изображений в blog стоит заменить raw `<img>` там, где это не blob preview.

### Public Listings

Endpoints:

- `GET /api/v1/public/listings`
- `GET /api/v1/public/listings/{listingId}`

Замечания:

- [x] [P1] Добавить query count test из-за риска N+1 в breadcrumbs. Выполнено 2026-06-21.
- [P1] Подготовить scoring/ranking слой до внедрения алгоритмизации.
- [P1] Не смешивать SQL-фильтрацию, сортировку и будущую ranking-формулу внутри repository.
- [P2] Добавить контрактные тесты на frontend для новых фильтров региона/города.

### Private Listings

Endpoints:

- `GET /api/v1/listings`
- `GET /api/v1/listings/favorites`
- `POST /api/v1/listings`
- `POST /api/v1/listings/{listingId}/archive`
- `POST /api/v1/listings/{listingId}/submit-for-review`
- `POST /api/v1/listings/{listingId}/media`
- `PATCH /api/v1/listings/{listingId}/media/reorder`
- `PATCH /api/v1/listings/{listingId}/media/{mediaId}/main`
- `DELETE /api/v1/listings/{listingId}/media/{mediaId}`
- `POST /api/v1/listings/{listingId}/favorite`
- `DELETE /api/v1/listings/{listingId}/favorite`
- `GET /api/v1/listings/{listingId}`
- `PATCH /api/v1/listings/{listingId}`
- `DELETE /api/v1/listings/{listingId}`

Замечания:

- [P1] Разделить listing form state на несколько hooks.
- [P1] Подготовить отдельный `ListingManagementMenu`/owner actions policy на frontend.
- [P2] Добавить throttle на media upload/favorite/listing mutations.
- [P2] Проверить lifecycle удаления media при удалении объявления.

## 8. Единый стиль и правила кодовой базы

### Что уже хорошо

- Backend использует `declare(strict_types=1)`.
- Backend проходит PHP CS Fixer.
- Frontend проходит ESLint и TypeScript build.
- Компоненты используют `Action` suffix для callback props там, где Next требует serializable props.

### Что нужно стандартизировать

- [P1] Зафиксировать архитектурные границы frontend:
  - `entities` не импортируют `features`.
  - `features` не импортируют `screens`.
  - `shared` не импортирует доменные сущности, кроме явно разрешенных типов.

- [P1] Добавить dependency boundary lint.
  - Можно использовать `eslint-plugin-boundaries` или внутренние path rules.

- [P1] Зафиксировать naming для props:
  - client callbacks: `onChange`, `onClick`;
  - server/action boundary callbacks: `onChangeAction`, `onSubmitAction`.
  - Сейчас в проекте уже были ошибки такого типа, поэтому правило лучше автоматизировать.

- [P2] Зафиксировать правила для API contracts:
  - каждый backend response имеет frontend zod schema;
  - каждый frontend API method использует общий parser;
  - никакого `as ApiDataResponse<...>` в feature API без wrapper.

- [P2] Зафиксировать правила размера файлов:
  - UI file > 300 строк требует декомпозиции;
  - hook > 250 строк требует декомпозиции;
  - service > 250 строк требует отдельного review;
  - repository > 250 строк требует разделения query/write.

## 9. Безопасность

- [x] [P1] Реализовать настоящий frontend session marker или удалить no-op. Выполнено 2026-06-19: no-op удален, источником истины остается серверная Sanctum-сессия.
- [x] [P1] Добавить security headers в Next config или на reverse proxy уровне.
  - Выполнено 2026-06-20: в `next.config.ts` добавлены CSP, HSTS для production, Permissions Policy, Referrer Policy, запрет framing и MIME sniffing, а также дополнительные legacy-защиты. CSP учитывает настроенный backend origin, внешние HTTPS-изображения и `blob:` previews; dev-only разрешения для HMR не попадают в production. Добавлены regression tests, правило подтверждено в production `routes-manifest`.
- [P2] Добавить throttle на listing/media/favorite endpoints.
- [P2] Проверить rate limits на location search, если появится autocomplete.
- [P2] Проверить ограничения media upload:
  - mime type;
  - размер;
  - количество;
  - расширение;
  - хранение и удаление старых файлов.
- [P2] Убедиться, что публичные listing responses никогда не содержат private fields.
  - Сейчас это покрыто feature test.
  - После рефакторинга mapper нужно сохранить этот test как safety net.

## 10. Производительность

- [x] [P1] Убрать потенциальный N+1 в `categoryBreadcrumbs()`. Выполнено 2026-06-21.
- [P1] Не строить будущую алгоритмизацию объявлений внутри `EloquentListingRepository`.
- [P2] Добавить индексы/поисковый слой для location text search.
- [P2] Добавить остальные query count tests; public listing list покрыт 2026-06-21.
- [P2] Добавить frontend request cancellation для фильтров/поиска.
- [P2] Проверить bundle impact крупных frontend sections и динамически грузить тяжелые части, например avatar editor/media editor/maps.

## 11. Тестирование

### Уже хорошо

- Backend feature tests покрывают auth, catalog, listing, media, news, location.
- Backend docs generation проверяется.
- Frontend имеет unit/integration tests.
- API contract tests на frontend есть.

### Что добавить

- [P1] E2E smoke suite:
  - registration/login/logout;
  - create listing draft;
  - create listing for review;
  - upload/reorder/delete media;
  - public listing filters;
  - favorites;
  - profile addresses.

- [ ] [P1] Backend query count tests:
  - [x] public listing list;
  - [ ] favorites;
  - [ ] own listing list;
  - [ ] show listing detail.

- [P2] Frontend tests for:
  - `PublicListingFilters` with region/city;
  - `ListingDetailsActions`;
  - account sidebar open/closed grid behavior;
  - header notification/theme/search layout.

- [P2] Contract generation:
  - рассмотреть генерацию frontend types/client из Scramble/OpenAPI.

## 12. Подготовка к алгоритмизации объявлений

Чтобы будущая алгоритмизация не превратилась в большой SQL `orderBy`, нужно заранее отделить ranking слой.

Рекомендуемая структура:

- `ListingRankingService`
- `ListingQualityScoreService`
- `ListingEngagementScoreService`
- `ListingSearchQueryBuilder`
- `ListingRecommendationService`
- `ListingImpressionLogger`

Минимальные события:

- `listing_viewed`
- `listing_card_impressed`
- `listing_card_clicked`
- `listing_favorited`
- `listing_contact_clicked`
- `listing_search_performed`

Поля/метрики:

- completeness score;
- media score;
- freshness score;
- seller trust score;
- location relevance;
- category relevance;
- engagement score;
- moderation penalty.

Правки перед алгоритмизацией:

- [P1] Вынести public listing query из repository.
- [P1] Добавить отдельную таблицу/модель для событий поведения.
- [P1] Добавить idempotent logging, чтобы не спамить просмотры.
- [P2] Добавить scheduled recalculation для listing scores.
- [P2] Подготовить Meilisearch/Typesense/Elasticsearch как отдельный адаптер, а не как часть repository.

## 13. Приоритетный план работ

### Этап 1. Стабилизация архитектуры

- [x] Реализовать/убрать no-op `auth-session.ts`. Выполнено 2026-06-19.
- [x] Разбить `settings-pages.tsx`. Выполнено 2026-06-19.
- [ ] Разбить `listing-form.tsx`.
- [ ] Разбить `use-listing-form-state.ts`.
- [x] Создать общий frontend API parser. Выполнено 2026-06-19.
- [x] Снизить количество ручных `as` в API layer. Выполнено 2026-06-19 для runtime-validated entity/feature API.

### Этап 2. Backend refactoring

- [x] Разделить `EloquentListingRepository`. Выполнено 2026-06-20.
- [ ] Объединить public/private listing payload mapping.
- [x] Вынести breadcrumbs в отдельный сервис/cache. Выполнено 2026-06-21.
- [ ] Вынести address snapshot в DTO.
- [ ] Добавить остальные query count tests; public listing list покрыт 2026-06-21.

### Этап 3. Контракты и e2e

- [ ] Добавить e2e smoke suite.
- [ ] Подумать о генерации frontend client/types из OpenAPI/Scramble.
- [ ] Ужесточить zod-схемы там, где API стабилен.
- [ ] Настроить `next.config.ts`.

### Этап 4. Масштабирование marketplace

- [ ] Добавить throttle на listing write/media/favorite.
- [ ] Подготовить ranking services.
- [ ] Добавить behavioral events.
- [ ] Подготовить search engine adapter.
- [ ] Оптимизировать location search.

## 14. Рекомендуемые commit messages

- `chore(audit): add technical audit report`
- `refactor(listing): split listing form state`
- `refactor(api): centralize frontend response parsing`
- `refactor(listing): extract listing payload assembler`
- `fix(listing): prevent category breadcrumb n plus one`
- `test(e2e): add listing management smoke tests`
- `feat(search): add listing ranking foundation`

## 15. Итоговое мнение

Кодовая база уже не выглядит как хаотичный MVP. В ней есть понятная архитектурная мысль, хорошие tests/checks и нормальная модульность. Главный риск на ближайший этап - не качество текущей реализации, а рост сложности вокруг объявлений: форма, адреса, медиа, фильтры, модерация, избранное, будущие карты и алгоритмизация.

Если сейчас аккуратно разделить крупные frontend hooks/components и backend listing services/repositories, дальнейшие задачи будут идти заметно быстрее и безопаснее. Самый важный следующий шаг - не добавлять новые возможности прямо в существующие крупные файлы, а сначала выделить отдельные слои для listing form state, payload mapping, public listing query и будущего ranking/search.
