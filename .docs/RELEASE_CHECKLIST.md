# Чеклист релиза backend

Документ описывает проверки перед релизом backend-части Snabix.

## 1. Объем изменений

- Проверь, какие домены затронуты.
- Убедись, что изменения не смешивают несвязанные задачи.
- Обнови `CHANGELOG.md`.
- Для новых API обнови DTO contracts и frontend schemas.
- Для новых env-переменных обнови `.env.example` и `SECRETS.md`.

## 2. Проверки backend

```bash
cd $PROJECT_ROOT/snabix-backend
task cs
vendor/bin/phpstan analyse --memory-limit=1G
docker compose exec -e APP_ENV=testing -e DB_HOST=db-test -e DB_DATABASE=snabix_test -e DB_CONNECTION=pgsql app php artisan test
```

Проверка API-документации:

```bash
docker compose exec app php artisan scramble:analyze
```

Проверка scheduled-команд:

```bash
docker compose exec app php artisan schedule:list
docker compose exec app php artisan shared:cleanup-storage --dry-run
docker compose exec app php artisan media:cleanup-orphans
```

Не запускай `media:cleanup-orphans --force` автоматически во время релиза. Сначала нужно просмотреть dry-run.

## 3. Безопасность базы

Перед миграциями проверь:

- окружение;
- имя базы;
- наличие backup для production;
- обратимость миграции;
- необходимость data migration.

Нельзя запускать против основной базы без явного намерения и backup:

```bash
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
```

Для тестов используется:

```text
APP_ENV=testing
DB_HOST=db-test
DB_DATABASE=snabix_test
```

## 4. Очереди и почта

Проверь queue worker:

```bash
docker compose ps queue-worker
docker compose logs --tail=100 queue-worker
```

Проверь failed jobs:

```bash
docker compose exec app php artisan queue:failed
```

Локальная почта:

```text
http://127.0.0.1:8025
```

## 5. Безопасность секретов

Перед push убедись:

- `.env` не добавлен;
- Telegram bot token не добавлен;
- backend service token не добавлен;
- SMTP password не добавлен;
- production DB password не добавлен;
- production env прошел проверку на dev placeholders:

```bash
PRODUCTION_ENV_FILE=/path/to/.env.production task secrets:production
```

- в staging/production не используются `SNABIX_BOT_SERVICE_TOKEN=change-me`, `RABBITMQ_USER=guest`, `RABBITMQ_PASSWORD=guest`, `DB_USERNAME=root`, `DB_PASSWORD=1234`;
- если placeholder успел попасть в staging/production, выполнена ротация соответствующего секрета и проверены access logs;
- user dumps не добавлены;
- новые private endpoints имеют auth middleware;
- новые bot endpoints имеют `bot.service`;
- новые admin resources имеют policies/permissions.

## 6. Storage

Проверка размера:

```bash
du -sh storage
du -sh storage/logs storage/api-docs storage/app/public storage/framework storage/debugbar
```

Безопасная техническая очистка:

```bash
php artisan shared:cleanup-storage --dry-run
```

Проверка orphan media:

```bash
php artisan media:cleanup-orphans
```

Реальное удаление orphan-файлов только после просмотра:

```bash
php artisan media:cleanup-orphans --days=7 --force
```

## 7. Ручной smoke

- `GET /api/v1/categories/list`.
- Регистрация пользователя.
- Вход пользователя.
- `GET /api/v1/auth/me`.
- Создание объявления.
- Загрузка изображения объявления.
- Добавление объявления в избранное.
- Получение уведомлений.
- Настройки уведомлений.
- Mailpit получает письмо.
- `/api/v1/service/bot/health` отвечает с корректным token.

## 8. Коммит и push

Проверка:

```bash
git status --short
git diff --check
```

Примеры сообщений:

```text
feat(): add notification preferences
fix(): handle listing media cleanup
refactor(): split listing normalizers
test(): cover storage cleanup command
docs(): update backend handbook
```

Push:

```bash
git push origin <branch>
```

## 9. Performance Budget Public Listings

Перед релизом зафиксируй backend-бюджет публичной витрины на staging:

- TTFB `GET /api/v1/public/listings`: не выше 500 ms.
- Query count для списка объявлений: не больше 12 SQL-запросов.
- N+1 по категориям, локациям, владельцу и медиа отсутствует.
- При превышении бюджета добавь комментарий причины, ссылку на задачу оптимизации и владельца.

## 10. План отката

Перед релизом должно быть понятно:

- какая миграция изменила схему;
- можно ли откатить миграцию;
- была ли data migration;
- какие env-переменные изменились;
- нужно ли перезапустить queue worker;
- зависит ли frontend от нового backend-контракта.

Если backend и frontend меняют контракт вместе, backend должен быть развернут первым только при сохранении обратной совместимости. Если обратной совместимости нет, сначала нужен compatibility layer.
