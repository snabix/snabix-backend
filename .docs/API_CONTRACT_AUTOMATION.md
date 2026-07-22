# Автоматизация API-контрактов

Backend публикует два разных по назначению артефакта:

- полный `storage/api-docs/openapi.json`, который генерируется Scramble из
  маршрутов, requests и responses и загружается CI как `snabix-backend-openapi`;
- выбранный consumer contract `contracts/listings.v1.json`, который защищает
  стабильные public/private listing DTO и копируется в frontend.

OpenAPI остается документацией всего HTTP API. Consumer snapshot намеренно
маленький: он содержит обязательные поля, public privacy denylist, примеры для
Zod и ссылки на четыре OpenAPI operations. Он не генерирует domain types и не
заменяет frontend adapters.

## Проверка

```bash
task contracts:check
```

Проверка экспортирует OpenAPI и подтверждает:

- наличие выбранных public/private listing operations и response schemas;
- структуру и версию consumer snapshot;
- отсутствие private полей в public example;
- наличие обязательных полей в public/private examples.

Feature-тест `ListingConsumerContractTest` проверяет те же required/forbidden
поля на реальных HTTP responses. Поэтому несовместимое удаление или rename поля
ломает backend CI даже тогда, когда OpenAPI пока описывает item как generic
object. Privacy assertions для `userId`, контактов, moderation reason и полного
media payload обязательны.

## Изменение контракта

1. Сначала определить, совместимо ли изменение и нужен ли deprecation window.
2. Обновить backend response, `contracts/listings.v1.json` и feature-тест.
3. Скопировать snapshot без ручного редактирования в
   `snabix-frontend/contracts/listings.v1.json`.
4. Обновить Zod adapter и выполнить `npm run contracts:check` во frontend.
5. Для несовместимого изменения увеличить `version` и сохранить compatibility
   adapter на согласованный период.

Snapshot содержит примеры wire DTO, но UI не должен импортировать JSON как
бизнес-модель. После валидации Zod adapter преобразует wire data в используемый
frontend-контракт.
