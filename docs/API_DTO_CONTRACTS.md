# API DTO Contracts

Дата фиксации: 2026-05-19

Этот документ фиксирует сложные response DTO, которые frontend использует как стабильный контракт. Scramble остается основным источником маршрутов и схем API, а здесь дополнительно описаны enum values, label-поля и различие public/private DTO.

## Enum Values And Labels

Все enum-поля в API отдаются числом, а рядом добавляется человекочитаемое label-поле для UI. Frontend не должен самостоятельно переводить числовые значения, если backend уже вернул `*Label`.

### ListingType

| Case | Value | Label |
| --- | ---: | --- |
| PRODUCT | 1 | Товар |
| SERVICE | 2 | Услуга |

### ListingStatus

| Case | Value | Label |
| --- | ---: | --- |
| DRAFT | 1 | Черновик |
| PENDING_REVIEW | 2 | На проверке |
| PUBLISHED | 3 | Опубликовано |
| REJECTED | 4 | Отклонено |
| ARCHIVED | 5 | В архиве |

### ListingCondition

| Case | Value | Label |
| --- | ---: | --- |
| NEW | 1 | Новый |
| USED | 2 | Б/у |
| NOT_APPLICABLE | 3 | Не применяется |

### CategoryCatalogType

| Case | Value | Label |
| --- | ---: | --- |
| PRODUCT | 1 | Товары |
| SERVICE | 2 | Услуги |

### CategoryAttributeType

| Case | Value | Label |
| --- | ---: | --- |
| TEXT | 1 | Текст |
| NUMBER | 2 | Число |
| BOOLEAN | 3 | Да/Нет |
| SELECT | 4 | Выбор одного значения |
| MULTISELECT | 5 | Выбор нескольких значений |
| DATE | 6 | Дата |

## Private Listing DTO

Используется в пользовательском кабинете и owner-сценариях:

| Endpoint | Назначение |
| --- | --- |
| `POST /api/v1/listings` | Создание объявления |
| `GET /api/v1/listings` | Список объявлений текущего пользователя |
| `GET /api/v1/listings/{listingId}` | Просмотр объявления текущего пользователя |
| `PATCH /api/v1/listings/{listingId}` | Обновление объявления текущего пользователя |
| `POST /api/v1/listings/{listingId}/submit-for-review` | Отправка черновика на проверку |

Private-only поля: `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason`.

```json
{
  "data": {
    "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
    "userId": "0196f6cf-7f9d-72f4-91df-d35f84efef10",
    "category": {
      "id": 12,
      "catalogType": 1,
      "catalogTypeLabel": "Товары",
      "parentId": 3,
      "name": "Смартфоны",
      "slug": "smartfony"
    },
    "type": 1,
    "typeLabel": "Товар",
    "status": 2,
    "statusLabel": "На проверке",
    "condition": 2,
    "conditionLabel": "Б/у",
    "title": "iPhone 14 Pro 256 GB",
    "slug": "iphone-14-pro-256-gb",
    "description": "Аккуратное состояние, полный комплект, без ремонта.",
    "price": 85000,
    "currency": "RUB",
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
        "type": 4,
        "typeLabel": "Выбор одного значения",
        "value": "256 GB",
        "displayValue": "256 GB"
      },
      {
        "attributeDefinitionId": 102,
        "name": "Доставка",
        "slug": "delivery",
        "type": 3,
        "typeLabel": "Да/Нет",
        "value": true,
        "displayValue": "Да"
      }
    ]
  }
}
```

## Private Listing Collection DTO

Личный список объявлений возвращает `items` и `meta` внутри `data`.

```json
{
  "data": {
    "items": [
      {
        "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
        "userId": "0196f6cf-7f9d-72f4-91df-d35f84efef10",
        "status": 1,
        "statusLabel": "Черновик",
        "type": 1,
        "typeLabel": "Товар",
        "condition": 2,
        "conditionLabel": "Б/у",
        "title": "iPhone 14 Pro 256 GB",
        "price": 85000,
        "currency": "RUB",
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

Используется для публичной витрины и карточек объявлений:

| Endpoint | Назначение |
| --- | --- |
| `GET /api/v1/public/listings` | Публичный список опубликованных объявлений |

Поддерживаемые query-фильтры: `categoryId`, `type`, `minPrice`, `maxPrice`, `sort`. Значения `sort`: `newest`, `oldest`, `price_asc`, `price_desc`, `popular`.

Поля `userId`, `contactName`, `contactPhone`, `contactEmail`, `rejectionReason` в public DTO не возвращаются. В `attributeValues` попадают только характеристики с `showInCard = true`.

```json
{
  "data": {
    "items": [
      {
        "id": "0196f6d4-5a72-72a1-97c4-7b9de6a0f8b1",
        "category": {
          "id": 12,
          "catalogType": 1,
          "catalogTypeLabel": "Товары",
          "parentId": 3,
          "name": "Смартфоны",
          "slug": "smartfony"
        },
        "type": 1,
        "typeLabel": "Товар",
        "status": 3,
        "statusLabel": "Опубликовано",
        "condition": 2,
        "conditionLabel": "Б/у",
        "title": "iPhone 14 Pro 256 GB",
        "slug": "iphone-14-pro-256-gb",
        "description": "Аккуратное состояние, полный комплект, без ремонта.",
        "price": 85000,
        "currency": "RUB",
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
            "type": 4,
            "typeLabel": "Выбор одного значения",
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

| Endpoint | Назначение |
| --- | --- |
| `GET /api/v1/categories/{categoryId}/attributes` | Получение характеристик категории с учетом наследования |

Frontend должен учитывать `placeholder`, `helpText`, `defaultValue`, `groupName`, `showInCard`, `isRequired`, `isFilterable`, `options` и `typeLabel`.

```json
{
  "data": [
    {
      "id": 101,
      "categoryId": 12,
      "name": "Память",
      "slug": "memory",
      "type": 4,
      "typeLabel": "Выбор одного значения",
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
