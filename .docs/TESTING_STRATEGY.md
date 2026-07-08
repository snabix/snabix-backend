# Стратегия тестирования backend

Backend-тесты должны проверять доменную логику, HTTP-контракты, storage-поведение, уведомления и CLI-команды без риска для основной базы.

## Главное правило безопасности

Тесты нельзя запускать против основной базы `snabix`.

Разрешенная тестовая конфигурация:

```text
APP_ENV=testing
DB_HOST=db-test
DB_DATABASE=snabix_test
DB_CONNECTION=pgsql
```

`Tests\TestCase` дополнительно проверяет окружение и падает, если конфигурация небезопасная.

## Полный запуск

```bash
cd /Users/dustun/Projects/snabix/snabix-backend
task test
```

## Точечные проверки

```bash
docker compose exec -e APP_ENV=testing -e DB_HOST=db-test -e DB_DATABASE=snabix_test -e DB_CONNECTION=pgsql app php artisan test tests/Feature/Listing
docker compose exec -e APP_ENV=testing -e DB_HOST=db-test -e DB_DATABASE=snabix_test -e DB_CONNECTION=pgsql app php artisan test tests/Feature/Notification
docker compose exec -e APP_ENV=testing -e DB_HOST=db-test -e DB_DATABASE=snabix_test -e DB_CONNECTION=pgsql app php artisan test tests/Feature/CLI
```

## Статический анализ и стиль

```bash
task cs
vendor/bin/phpstan analyse --memory-limit=1G
php artisan scramble:analyze
```

## Что покрываем

- Auth: регистрация, вход, выход, email verification, reset password.
- Sessions: список активных сессий и завершение сессий.
- Profile: профиль, аватар, адреса.
- Catalog: категории, ветки, характеристики.
- Listing: создание, обновление, удаление, статусы, избранное.
- Media: загрузка, замена, удаление, порядок, orphan cleanup.
- Notifications: preferences, database channel, mail channel, read/delete state.
- CLI: admin-команды, cleanup-команды.
- Docs/contracts: Scramble и DTO-контракты.

## Когда добавлять тест

Тест обязателен, если меняется:

- статус объявления;
- API response shape;
- права доступа;
- upload/delete media;
- notification delivery;
- cleanup-команда;
- миграция с важной бизнес-логикой;
- обработка 401/419;
- service API для bot.

## Что не делать

Нельзя:

- отключать safety-проверку тестовой БД;
- запускать `migrate:fresh` на `snabix`;
- использовать реальные пользовательские данные в тестах;
- мокать доменную логику вместо проверки use case без причины;
- добавлять `@phpstan-ignore`, если можно исправить тип.

## Ручной smoke backend

После крупных изменений проверь:

- `GET /api/v1/categories/list`;
- регистрацию и вход;
- `GET /api/v1/auth/me`;
- создание объявления;
- загрузку изображения;
- добавление в избранное;
- список уведомлений;
- настройки уведомлений;
- Mailpit;
- `/api/v1/service/bot/health`.

## Приоритеты дальнейшего покрытия

Высокий приоритет:

- admin status actions для объявлений;
- notification preferences end-to-end;
- orphan media cleanup edge cases;
- bot service API unavailable scenarios.

Средний приоритет:

- category cache invalidation;
- news block contracts;
- Filament form smoke tests;
- storage retention config edge cases.
