# Локальная разработка backend

Документ описывает локальный запуск и обслуживание backend-части Snabix.

## Назначение сервиса

Backend отвечает за:

- REST API `/api/v1`;
- авторизацию через Laravel Sanctum;
- пользователей, профиль, адреса и активные сессии;
- каталог категорий и характеристик;
- объявления, статусы, избранное и медиа;
- уведомления на сайте и email;
- Filament admin panel;
- service API для Telegram-бота;
- очереди, storage и maintenance-команды.

## Первый запуск

```bash
cd /Users/dustun/Projects/snabix/snabix-backend
cp .env.example .env
composer install
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link
```

Для Taskfile-команд нужен CLI `go-task`. Расширение VS Code само CLI не устанавливает:

```bash
brew install go-task
task --version
```

Локальные адреса:

- backend: `http://127.0.0.1:8080`;
- admin panel: `http://127.0.0.1:8080/admin`;
- Mailpit: `http://127.0.0.1:8025`;
- RabbitMQ management: `http://127.0.0.1:15672`.

## Docker-сервисы

- `app`: PHP-FPM приложение Laravel.
- `queue-worker`: обработчик очереди `notifications`.
- `caddy`: HTTP-сервер на порту `8080`.
- `db`: основная PostgreSQL база `snabix`.
- `db-test`: тестовая PostgreSQL база `snabix_test`.
- `redis`: cache и lock-хранилище.
- `rabbitmq`: брокер очередей.
- `mailpit`: локальный SMTP-перехватчик.

## Ежедневная работа

```bash
docker compose up -d
docker compose ps
docker compose logs -f app
docker compose logs -f queue-worker
```

Artisan-команды:

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate
docker compose exec app php artisan queue:restart
docker compose exec app php artisan schedule:list
```

## Почта и очереди

В локальной среде письма не уходят во внешний интернет. Их перехватывает Mailpit.

Проверка:

```bash
docker compose up -d mailpit rabbitmq queue-worker
docker compose exec app php artisan queue:failed
```

Если письма не появляются:

- проверь `MAIL_HOST=mailpit`;
- проверь, что `queue-worker` запущен;
- проверь failed jobs;
- проверь, что событие действительно создает notification.

## Storage

Техническая очистка:

```bash
docker compose exec app php artisan shared:cleanup-storage --dry-run
```

Поиск постоянных медиа без записи в БД:

```bash
docker compose exec app php artisan media:cleanup-orphans
```

Реальное удаление orphan-файлов только после просмотра dry-run:

```bash
docker compose exec app php artisan media:cleanup-orphans --days=7 --force
```

Постоянные файлы `storage/app/public/images/...` нельзя удалять по возрасту. Их можно удалять только через доменную логику или через orphan-проверку с БД.

## Безопасная работа с базой

Нельзя запускать destructive-команды против основной базы `snabix`:

```bash
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
```

Для тестов используется только `db-test/snabix_test`.

## Проверки качества

```bash
task cs
vendor/bin/phpstan analyse --memory-limit=1G
task test
```

Для API-документации:

```bash
docker compose exec app php artisan scramble:analyze
```

## Частые проблемы

### Ошибка Redis в локальном PHP

Если команда запускается вне контейнера и падает из-за `Class "Redis" not found`, запускай ее внутри Docker:

```bash
docker compose exec app php artisan <command>
```

Либо используй Predis/корректную локальную PHP-конфигурацию.

### Клиент получает 401 или 419

Проверь:

- `SANCTUM_STATEFUL_DOMAINS`;
- `SESSION_DOMAIN`;
- `APP_URL`;
- `FRONTEND_URL`;
- cookies в браузере.

### Категории или объявления не загружаются

Проверь:

- `docker compose ps`;
- `route:list`;
- ошибки в `storage/logs`;
- соответствие frontend API base URL.
