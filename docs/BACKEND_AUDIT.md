# Backend Audit

Дата: 2026-05-17

## Статус Проверки

- [x] Проверены основные bounded contexts: Auth, Catalog, Listing, Media, Shared.
- [x] Проверены API routes и текущий контракт frontend/backend.
- [x] Проверены тесты, PHP CS Fixer и PHPStan.
- [x] Исправлен response metadata для `GET /api/v1/categories/{categoryId}/attributes`.
- [x] Добавлен тест на metadata характеристик категории.
- [ ] Провести отдельный security review перед production.
- [ ] Провести performance review запросов каталога и объявлений после появления реального объема данных.

## Выполненные Исправления

- [x] `GetCategoryAttributesHandler` теперь возвращает полную metadata для frontend-форм:
  - `placeholder`;
  - `helpText`;
  - `defaultValue`;
  - `groupName`;
  - `showInCard`.
- [x] Добавлен feature-тест, который фиксирует этот контракт.
- [x] Ранее добавлен `CatalogDemoSeeder` и тест идемпотентности.
- [x] Ранее создание объявления разделено на `pending_review` и `draft` через `saveAsDraft`.
- [x] Добавлен отдельный endpoint `submit-for-review` для отправки черновика объявления на проверку.
- [x] Добавлена `ListingPolicy` для централизованной авторизации действий над объявлениями.
- [x] Добавлена пагинация для публичного и личного списка объявлений.
- [x] Добавлены простые фильтры личных объявлений по статусу, типу и категории.
- [x] Добавлен audit trail для создания, отправки на проверку и удаления объявлений.
- [x] Добавлены проверки `auth:admin`, user-session изоляции и CSRF для SPA-сценария API характеристик категорий.
- [x] Required-характеристики объявлений проверяются в application service, а не внутри persistence-синхронизации.
- [x] Request-классы получили методы для вычисляемых входных данных, чтобы разгрузить контроллеры.
- [x] Добавлен endpoint смены пароля авторизованного пользователя.
- [x] Application cooldown повторной отправки email verification зафиксирован тестами.
- [x] Auth-события дополнены логами для смены пароля и повторного запроса email verification.
- [x] API-контракт истекшей сессии стандартизирован для `401` и `419`.
- [x] Добавлен документ `docs/API_DTO_CONTRACTS.md` с response examples для сложных frontend DTO, enum values/labels и границами public/private listing DTO.
- [x] Для категорий сохранена строгая глобальная политика уникальности `slug`.
- [x] Характеристики категорий получили `dependency_rules` для будущей динамической видимости полей.
- [x] Характеристики категорий получили `schema_version`, а значения объявлений сохраняют snapshot схемы характеристики.
- [x] Удаление характеристики блокируется, если по ней уже есть значения в объявлениях.
- [x] Добавлены admin API endpoints bulk import/export характеристик категорий.

## Архитектура

Backend сейчас построен вокруг DDD-подхода с Laravel-практиками:

- `Domain` содержит контракты, enum, value objects, events.
- `Application` содержит use cases, handlers, services, support mappers.
- `Http` содержит controller/request/response по сценариям.
- `Infrastructure` содержит Eloquent models, repositories, providers.
- `Filament` вынесен отдельно внутри bounded context.

### Что Хорошо

- Use-case слой читаемый и предсказуемый.
- HTTP-слой декомпозирован по сценариям, а не свален в один controller.
- Есть repository contracts для ключевых операций.
- Backend API покрыт feature-тестами.
- PHPStan level 9 проходит без ошибок.
- Тесты изолированы через `snabix_test`, есть защита от запуска на основной БД.
- Scramble используется как единый источник API-документации.
- Media storage имеет тесты на replace/delete/move.
- Queue/RabbitMQ уже отделены на инфраструктурном уровне.

### Что Нарушено Или Рисково

- [x] `EloquentListingRepository` содержит много бизнес-валидации характеристик. Правила публикации/черновика вынесены в `ListingPublicationPolicy`.
- [x] `syncAttributeValues()` вынесен из repository в `ListingAttributeValueSynchronizer`.
- [x] В listing update пока нет явного action `submitForReview`. Сейчас update сохраняет текущий статус, но для UX “опубликовать черновик” понадобится отдельный endpoint.
- [x] Переходы статусов объявления централизованы в `ListingStatusTransitionPolicy`.
- [x] Public listing API пока без пагинации.
- [x] List owned listings API тоже без пагинации и фильтров.
- [x] Нет отдельной политики авторизации на уровне Policy/Gate для listing actions.
- [x] Admin category attribute API защищен `auth:admin`, но нужно проверить CSRF/session-guard сценарий отдельно для SPA/admin.
- [x] Валидация request классов проверяет базовые типы, но category-specific required attributes проверяются в repository. Лучше поднять это в application service.
- [x] Нет audit trail для ключевых действий с объявлениями: создание, отправка на проверку, публикация, отклонение, удаление.

## Auth

- [x] Реализованы sign-up/sign-in/logout/profile/verify/reset flows.
- [x] Email verification и password reset покрыты тестами.
- [x] RabbitMQ queue используется для email jobs.
- [x] Нужна политика refresh/session expiration на frontend/backend уровне.
- [x] Нужна защита от частых resend verification не только throttle, но и cooldown в доменной логике.
- [x] Нужен endpoint смены пароля авторизованного пользователя.
- [x] Нужны события и логи для критичных auth-действий в едином формате.

## Catalog

- [x] Есть дерево категорий.
- [x] Есть импорт категорий.
- [x] Есть характеристики категорий с наследованием через `applies_to_children`.
- [x] Есть demo-сидер категорий и характеристик.
- [x] Нужна уникальность slug в рамках parent или строгая глобальная политика slug.
- [x] Нужна поддержка dependency rules для характеристик: поле B видно только если поле A имеет значение X.
- [x] Нужна версия схемы характеристик, чтобы старые объявления не ломались после изменения формы категории.
- [x] Нужна admin-защита от удаления характеристики, если по ней уже есть значения в объявлениях.
- [x] Нужна bulk-операция импорта/экспорта характеристик.

## Listings

- [x] Create/show/update/list/delete пользовательских объявлений есть.
- [x] Public listings отделены от owned listings.
- [x] Public mapper не раскрывает owner/contact поля.
- [x] Create listing больше не принимает модерационные поля от пользователя.
- [x] Обычное создание переводит объявление в `pending_review`.
- [x] Черновик создается только через `saveAsDraft`.
- [x] Нужен endpoint `POST /api/v1/listings/{id}/submit-for-review`.
- [ ] Нужен admin moderation flow: publish/reject/archive.
- [x] Переходы статусов централизованы через `ListingStatusTransitionPolicy`.
- [ ] Нужны media attachments для объявлений.
- [ ] Нужны search/filter/sort endpoints.
- [x] Нужна пагинация и cursor/offset strategy.
- [ ] Нужны индексы под будущий поиск по category/status/price/published_at.

## Media

- [x] Единое media storage реализовано через Spatie Media Library.
- [x] Есть Filament resource и preview.
- [x] Есть replace/delete tests.
- [ ] Нужна привязка media к объявлениям.
- [ ] Нужны conversions/thumbnails для изображений объявлений.
- [ ] Нужна политика visibility для публичных/приватных файлов на уровне access-control.
- [ ] Нужна очистка orphan media.

## Filament

- [x] Ресурсы вынесены по bounded contexts.
- [x] Глобально отключена `Создать и создать еще`.
- [x] Ресурс характеристик имеет заголовки и breadcrumbs.
- [ ] Нужны роли/permissions для admin actions.
- [ ] Нужна отдельная dashboard-страница модерации объявлений.
- [ ] Нужна фильтрация media/listings/category attributes по ключевым полям.

## API Документация

- [x] Scramble проходит анализ.
- [x] Нужно добавить больше response examples для сложных DTO.
- [x] Нужно явно описать enum values и label-поля для frontend.
- [x] Нужно зафиксировать public vs private listing DTO.

## Тестирование

Текущий результат:

- `task cs` прошел.
- `task test` прошел: 44 теста, 224 assertions.
- `php -d memory_limit=1G vendor/bin/phpstan analyse --debug` прошел без ошибок.

Что добавить:

- [x] Тест `submit-for-review`, когда endpoint появится.
- [ ] Тесты status transition policy.
- [x] Тесты пагинации public/owned listings.
- [ ] Тесты media attachments для listings.
- [ ] Тесты удаления category attribute при наличии listing values.
- [x] Тесты authorization policies для чужих объявлений на update/delete.

## Рекомендованный План

1. [x] Вынести синхронизацию характеристик объявления из repository в отдельный synchronizer.
2. [x] Добавить state machine/policy для статусов объявления.
3. [x] Добавить endpoint отправки черновика на проверку.
4. [ ] Добавить moderation actions в admin API/Filament.
5. [ ] Добавить media attachments для listings.
6. [x] Добавить пагинацию и фильтры.
7. [x] Зафиксировать OpenAPI/Scramble examples для frontend DTO.

## Ответ На Архитектурный Вопрос По Категорийным Формам

Текущая реализация подходит как MVP-фундамент: форма объявления строится из `category_attribute_definitions`, а значения сохраняются отдельно в `listing_attribute_values`.

Для большого количества категорий я бы развивал это как версионируемую схему формы:

- `category_attribute_definitions` описывает поля формы.
- `applies_to_children` дает наследование от родительских категорий.
- `group_name` группирует поля в UI.
- `sort_order` задает порядок.
- `type/options/default_value/placeholder/help_text` управляют рендерингом frontend.
- В будущем добавить `visibility_rules` и `validation_rules` как JSON-структуры.
- В будущем добавить `schema_version`, чтобы объявление знало, по какой версии формы оно было создано.

Главная идея: backend остается источником схемы формы, frontend только рендерит ее по типам и отправляет значения. Это масштабируемее, чем писать отдельную React/PHP-форму под каждую категорию.
