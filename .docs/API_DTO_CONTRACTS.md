# API DTO Contracts

Дата фиксации: 2026-07-19

Этот документ фиксирует сложные response DTO, которые frontend использует как стабильный контракт. Scramble остается основным источником маршрутов и схем API, а здесь дополнительно описаны enum values, label-поля и различие public/private DTO.

## Идемпотентные create-запросы

`POST /api/v1/auth/sign-up` и `POST /api/v1/users/{userId}/reviews` принимают необязательный header `Idempotency-Key`.

- формат: 8-128 латинских букв, цифр или символов `. _ : -`;
- клиент генерирует новый непрогнозируемый ключ для нового действия;
- после timeout или потери ответа клиент повторяет запрос с тем же ключом и неизменным payload;
- одинаковый ключ, actor и payload в течение 24 часов возвращают ранее созданный ресурс без повторной записи;
- одинаковый ключ с измененным payload возвращает `409` и code `request.idempotency-conflict`;
- повтор email или review с другим ключом остается бизнес-ошибкой `422`.

Срок гарантированного replay задается backend-параметром `IDEMPOTENCY_RETENTION_HOURS`. Backend хранит только HMAC actor/key/payload fingerprint, а не исходный ключ или пароль.

## Conventions имен полей

| Слой | Правило | Пример |
|------|---------|--------|
| PostgreSQL | `snake_case`; внешний ключ заканчивается на `_id` | `user_id`, `published_at` |
| PHP domain/application | `camelCase`; имя отражает смысл, а не тип хранения | `$listingStatus`, `$priceAmountMinor` |
| JSON request/response | `lowerCamelCase`; relation/reference заканчивается на `Id` | `categoryId`, `publishedAt` |
| Public ID | непрозрачная строка UUID/ULID; клиент не извлекает из нее смысл | `"0196f6d4-..."` |
| Timestamp | `...At`, ISO 8601 с timezone; backend сериализует UTC | `"2026-05-19T10:00:00+00:00"` |
| Date without time | `YYYY-MM-DD`; suffix `At` не используется | `dateOfBirth: "1994-05-12"` |
| Money | integer minor units + ISO 4217 currency | `priceAmountMinor: 85000`, `priceCurrency: "RUB"` |
| Enum | предметное имя поля + стабильное string value | `listingStatus: "pendingReview"` |
| Boolean | префикс `is`, `has` или `can` | `isNegotiable` |

Общие поля `type` и `status` запрещены для новых resource DTO. Исключение: локальный discriminated union, где `type` является строковым discriminator, например `contentBlocks[].type`. Числовое значение enum допустимо внутри DB/domain, но не является каноническим API-контрактом.

`fullName` означает вычисленное полное имя/название, `description` — пользовательское описание. В JSON нельзя вводить варианты `fullname` или `about`; DB-колонки с историческим именем нормализуются mapper-ом.

## Enum Values And Labels

Frontend использует string value для логики и `*Label` для отображения. Числа в таблице приведены только как внутреннее DB/domain-представление.

| API field | String values | DB values |
|-----------|---------------|-----------|
| `listingKind` | `product`, `service` | `1`, `2` |
| `listingStatus` | `draft`, `pendingReview`, `published`, `rejected`, `archived` | `1`-`5` |
| `itemCondition` | `new`, `used`, `notApplicable` | `1`-`3` |
| `catalogKind` | `product`, `service` | `1`, `2` |
| `valueType` | `text`, `number`, `boolean`, `select`, `multiSelect`, `date` | `1`-`6` |
| `publicationStatus` | `draft`, `published`, `archived` | `1`-`3` |
| `reviewStatus` | `published`, `hidden`, `rejected` | string enum |

## Compatibility и deprecation

Переходный период для aliases `type`, `typeLabel`, `status`, `statusLabel`, `condition`, `conditionLabel`, `price`, `currency`, `catalogType`, `catalogTypeLabel`, а также numeric `attributeValues[].type` и `contentBlocks[].typeValue` заканчивается **2026-10-31**. Для review DTO `status`/`statusLabel` заменены на `reviewStatus`/`reviewStatusLabel`.

- backend до этой даты принимает старые request-поля и возвращает старые response aliases вместе с каноническими;
- если canonical и legacy request-поле переданы одновременно, запрос отклоняется как неоднозначный;
- frontend разворачивается первым: он отправляет canonical fields, но compatibility adapter умеет прочитать legacy-only ответ; backend можно откатить независимо, а откат frontend после backend выполняется координированно;
- adapter удаляет legacy aliases до передачи данных в entities/screens;
- удаление aliases раньше даты запрещено; после даты выполняется отдельной задачей с release notes и contract-test update.

## Private Listing DTO

## Приватный DTO объявления

Используется в пользовательском кабинете и owner-сценариях:

| Endpoint                                              | Назначение                                  |
|-------------------------------------------------------|---------------------------------------------|
| `POST /api/v1/listings`                               | Создание объявления                         |
| `GET /api/v1/listings`                                | Список объявлений текущего пользователя     |
| `GET /api/v1/listings/{listingId}`                    | Просмотр объявления текущего пользователя   |
| `PATCH /api/v1/listings/{listingId}`                  | Обновление объявления текущего пользователя |
| `POST /api/v1/listings/{listingId}/submit-for-review` | Отправка черновика на проверку              |

Private-only поля: `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`, `media`.

```json
{
  "data": {
    "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
    "userId": "0196f6cf-7f9d-72f4-91df-d35f84efef10",
    "category": {
      "id": 12,
      "catalogKind": "product",
      "catalogKindLabel": "Товары",
      "parentId": 3,
      "name": "Смартфоны",
      "slug": "smartfony"
    },
    "listingKind": "product",
    "listingKindLabel": "Товар",
    "listingStatus": "pendingReview",
    "listingStatusLabel": "На проверке",
    "itemCondition": "used",
    "itemConditionLabel": "Б/у",
    "title": "iPhone 14 Pro 256 GB",
    "slug": "iphone-14-pro-256-gb",
    "description": "Аккуратное состояние, полный комплект, без ремонта.",
    "priceAmountMinor": 85000,
    "priceCurrency": "RUB",
    "isNegotiable": true,
    "contactName": "Имран",
    "contactPhone": "+79991234567",
    "contactEmail": "seller@example.com",
    "viewsCount": 12,
    "isFeatured": false,
    "rejectionReason": null,
    "publishedAt": null,
    "expiresAt": null,
    "attributeValues": [
      {
        "attributeDefinitionId": 101,
        "name": "Память",
        "slug": "memory",
        "valueType": "select",
        "valueTypeLabel": "Выбор одного значения",
        "value": "256 GB",
        "displayValue": "256 GB"
      },
      {
        "attributeDefinitionId": 102,
        "name": "Доставка",
        "slug": "delivery",
        "valueType": "boolean",
        "valueTypeLabel": "Да/Нет",
        "value": true,
        "displayValue": "Да"
      }
    ]
  }
}
```

## Приватный DTO коллекции объявлений

Личный список объявлений возвращает `items` и `meta` внутри `data`.

```json
{
  "data": {
    "items": [
      {
        "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
        "userId": "0196f6cf-7f9d-72f4-91df-d35f84efef10",
        "listingStatus": "draft",
        "listingStatusLabel": "Черновик",
        "listingKind": "product",
        "listingKindLabel": "Товар",
        "itemCondition": "used",
        "itemConditionLabel": "Б/у",
        "title": "iPhone 14 Pro 256 GB",
        "priceAmountMinor": 85000,
        "priceCurrency": "RUB",
        "isNegotiable": true,
        "contactName": "Имран",
        "contactPhone": "+79991234567",
        "contactEmail": "seller@example.com",
        "rejectionReason": null,
        "attributeValues": []
      }
    ],
    "meta": {
      "currentPage": 1,
      "perPage": 12,
      "lastPage": 4,
      "total": 39
    }
  }
}
```

## Public Listing DTO

## Публичный DTO объявления

Используется для публичной витрины и карточек объявлений:

| Endpoint                                            | Назначение                                             |
|-----------------------------------------------------|--------------------------------------------------------|
| `GET /api/v1/public/listings`                       | Публичный список опубликованных объявлений             |
| `GET /api/v1/public/listings/{listingId}`           | Публичная карточка опубликованного объявления           |
| `POST /api/v1/listings/{listingId}/favorite`        | Добавление в избранное с публичной карточкой в ответе   |
| `DELETE /api/v1/listings/{listingId}/favorite`      | Удаление из избранного с публичной карточкой в ответе   |
| `GET /api/v1/listings/favorites`                    | Избранные объявления в формате публичных карточек       |

Поддерживаемые query-фильтры: `categoryId`, `listingKind`, `minPriceAmountMinor`, `maxPriceAmountMinor`, `sort`. Значения `sort`: `newest`, `oldest`, `price_asc`, `price_desc`, `popular`.

Поля `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`, `media` в public DTO не возвращаются. В `attributeValues` попадают только характеристики с `showInCard = true`.

Авторизация favorite endpoints дает право управлять связью избранного, но не
расширяет видимость объявления. Даже собственное объявление в избранном
возвращается как public card DTO; owner/private projection доступна через
`/api/v1/listings` и `/api/v1/listings/{listingId}`.

```json
{
  "data": {
    "items": [
      {
        "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
        "category": {
          "id": 12,
          "catalogKind": "product",
          "catalogKindLabel": "Товары",
          "parentId": 3,
          "name": "Смартфоны",
          "slug": "smartfony"
        },
        "listingKind": "product",
        "listingKindLabel": "Товар",
        "listingStatus": "published",
        "listingStatusLabel": "Опубликовано",
        "itemCondition": "used",
        "itemConditionLabel": "Б/у",
        "title": "iPhone 14 Pro 256 GB",
        "slug": "iphone-14-pro-256-gb",
        "description": "Аккуратное состояние, полный комплект, без ремонта.",
        "priceAmountMinor": 85000,
        "priceCurrency": "RUB",
        "isNegotiable": true,
        "viewsCount": 12,
        "isFeatured": false,
        "publishedAt": "2026-05-19T10:00:00+00:00",
        "expiresAt": "2026-06-18T10:00:00+00:00",
        "attributeValues": [
          {
            "attributeDefinitionId": 101,
            "name": "Память",
            "slug": "memory",
            "valueType": "select",
            "valueTypeLabel": "Выбор одного значения",
            "value": "256 GB",
            "displayValue": "256 GB"
          }
        ]
      }
    ],
    "meta": {
      "currentPage": 1,
      "perPage": 24,
      "lastPage": 8,
      "total": 188
    }
  }
}
```

## Category Attribute DTO

Используется для динамической формы объявления:

| Endpoint                                         | Назначение                                              |
|--------------------------------------------------|---------------------------------------------------------|
| `GET /api/v1/categories/{categoryId}/attributes` | Получение характеристик категории с учетом наследования |

Frontend должен учитывать `placeholder`, `helpText`, `defaultValue`, `groupName`, `showInCard`, `isRequired`, `isFilterable`, `options` и `valueTypeLabel`.

```json
{
  "data": [
    {
      "id": 101,
      "categoryId": 12,
      "name": "Память",
      "slug": "memory",
      "valueType": "select",
      "valueTypeLabel": "Выбор одного значения",
      "unit": null,
      "description": "Объем встроенной памяти устройства.",
      "placeholder": "Выберите объем памяти",
      "helpText": "Укажите фактический объем памяти товара.",
      "defaultValue": null,
      "dependencyRules": [
        {
          "attributeSlug": "brand",
          "operator": "equals",
          "value": "Apple"
        }
      ],
      "groupName": "Характеристики устройства",
      "options": [
        "64 GB",
        "128 GB",
        "256 GB",
        "512 GB"
      ],
      "isRequired": true,
      "isFilterable": true,
      "showInCard": true,
      "isActive": true,
      "appliesToChildren": true,
      "schemaVersion": 1,
      "sortOrder": 10
    }
  ]
}
```

## Error DTO For Expired Session

Эти ответы нужны frontend для единой политики истечения сессии.

```json
{
  "message": "Unauthenticated.",
  "error": "auth.unauthenticated"
}
```

```json
{
  "message": "CSRF token mismatch.",
  "error": "auth.csrf-token-mismatch"
}
```
