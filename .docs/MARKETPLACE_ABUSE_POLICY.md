# Marketplace verification and abuse policy

Дата фиксации policy: `2026-07-22`.

## Цели

Policy ограничивает автоматизированное создание контента и чрезмерную нагрузку, но не должна мешать обычному просмотру витрины, поисковым роботам или владельцу управлять уже созданными данными.

Основные правила:

- публичное чтение разделено на независимые buckets по типу ресурса;
- authenticated mutations имеют отдельные user и IP buckets;
- email verification обязательна до создания нового trust-sensitive контента;
- изменение или удаление уже принадлежащего пользователю объявления не блокируется после смены email;
- request body, email, токены и другие credentials не записываются в abuse logs;
- значение IP разрешено использовать только после корректной настройки trusted proxy chain.

## Матрица лимитов

Конфигурация является source of truth: `config/marketplace-abuse.php`.

| Scope | Маршруты | User limit | IP limit | Verification |
|---|---|---:|---:|---|
| `catalog_read` | категории и атрибуты | - | 1200/мин | нет |
| `location_read` | регионы и города | - | 1200/мин | нет |
| `listing_read` | публичный список и карточка | - | 600/мин | нет |
| `news_read` | список и запись новостей | - | 600/мин | нет |
| `review_read` | публичные отзывы продавца | - | 600/мин | нет |
| `account_listing_read` | свои объявления и избранное | 180/мин | 1800/мин | auth |
| `listing_create` | создание объявления или черновика | 10/час | 100/час | email |
| `listing_write` | update, archive, delete | 60/мин | 600/мин | auth |
| `listing_submit` | отправка на модерацию | 20/час | 200/час | email |
| `listing_media_write` | upload, reorder, main, delete media | 60/час | 600/час | email только для upload |
| `favorite_write` | добавить или удалить избранное | 120/мин | 1200/мин | auth |
| `review_create` | публикация отзыва | 5/час | 100/час | email |

Лимит начинается с первого запроса scope и сбрасывается после `decay_seconds`. User и IP buckets применяются одновременно; блокировка происходит при исчерпании любого из них. Laravel хэширует итоговые rate-limit keys перед сохранением в cache store.

## Почему public limits разделены

Один общий низкий лимит для всех GET endpoints мог бы блокировать индексатор после обхода каталога и одновременно закрывать ему новости, отзывы и объявления. Независимые buckets ограничивают локальный всплеск и сохраняют доступность остальных разделов.

User-Agent не используется для обхода rate limit: его легко подделать. Для согласованного crawler traffic следует использовать `robots.txt`, sitemap, CDN/cache и наблюдение за реальными `429`, а не бесконтрольный allowlist по заголовку.

IP limits для authenticated actions намеренно существенно выше user limits. Они являются backstop против массовой автоматизации через несколько аккаунтов и не должны наказывать пользователей мобильного CGNAT, корпоративного Wi-Fi или другого общего NAT-адреса.

## High-risk verification

Неподтвержденный пользователь получает `403`:

```json
{
  "message": "Подтвердите email, чтобы выполнить это действие.",
  "code": "auth.email-verification-required",
  "verificationRequired": true
}
```

Verification обязательна для создания объявления, отправки его на модерацию, загрузки нового файла и публикации отзыва. Update/archive/delete, удаление media и favorites требуют авторизацию и rate limit, но не повторную verification. Это сохраняет право пользователя исправить или удалить собственные данные после изменения email.

## Rate-limit response

Превышение лимита возвращает `429`, стандартные `Retry-After`/`X-RateLimit-*` headers и body:

```json
{
  "message": "Слишком много запросов. Повторите попытку позже.",
  "code": "abuse.rate-limit-exceeded",
  "retryAfterSeconds": 42
}
```

Клиент должен отключить повторную отправку на `retryAfterSeconds`, не запускать tight retry loop и сохранить введенные пользователем данные формы.

## Structured abuse events

Отказы записываются в `system_logs` с `category=abuse`, уровнем `warning` и стабильным action. Context содержит только:

- `reason`: `email_verification_required` или `rate_limit_exceeded`;
- `policy_version`;
- для `429`: `scope`, `dimension`, `retry_after_seconds`.

HTTP method, normalized path, status, IP, User-Agent и UUID пользователя сохраняются в выделенных колонках system log. Payload запроса не сохраняется.

## Эксплуатация

- Production cache для counters должен быть общим для всех replicas, текущий стандарт проекта - Redis.
- Перед горизонтальным масштабированием необходимо проверить, что все app replicas используют один `CACHE_STORE` и prefix.
- Caddy/CDN/LB должны передавать реальный client IP, а Laravel должен доверять только известной proxy chain. До этого IP bucket может объединить разных пользователей либо довериться spoofed header.
- Изменение лимитов выполняется по метрикам `429`, abuse logs, p95 и support incidents. Ослабление не должно происходить только из-за одного User-Agent.
- Новая marketplace mutation обязана получить отдельный или явно выбранный существующий scope и route-contract test.

## Проверки

`tests/Feature/Shared/MarketplaceAbusePolicyTest.php` фиксирует:

- запрет high-risk actions для unverified user;
- JSON/header contract `429`;
- сброс счетчика после decay window;
- независимость public buckets;
- наличие специализированного middleware на каждом marketplace route.
