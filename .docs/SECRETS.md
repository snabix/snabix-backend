# Секреты backend

Документ описывает backend-секреты Snabix и правила безопасной работы с ними.

## Главное правило

Реальные секреты нельзя коммитить.

Можно хранить в репозитории:

- `.env.example` с placeholder-значениями;
- названия переменных окружения;
- инструкции по генерации и ротации.

Значения из `.env.example` предназначены только для локальной Docker-среды. Их нельзя переносить в staging или production.

Нельзя хранить:

- реальный `APP_KEY`;
- пароли БД;
- SMTP-пароли;
- токен Telegram-бота;
- service token для bot API;
- дампы пользователей;
- приватные ключи.

## Основной файл окружения

Локальный backend env:

```text
$PROJECT_ROOT/snabix-backend/.env
```

Пример хранится в:

```text
$PROJECT_ROOT/snabix-backend/.env.example
```

## Критичные переменные

```env
APP_KEY=
DB_PASSWORD=
REDIS_PASSWORD=
RABBITMQ_PASSWORD=
MAIL_USERNAME=
MAIL_PASSWORD=
SNABIX_BOT_SERVICE_TOKEN=
```

Назначение:

- `APP_KEY`: шифрование Laravel-данных.
- `DB_PASSWORD`: доступ к PostgreSQL.
- `REDIS_PASSWORD`: доступ к Redis, если включен пароль.
- `RABBITMQ_PASSWORD`: доступ к RabbitMQ.
- `MAIL_USERNAME` и `MAIL_PASSWORD`: SMTP-доступ.
- `SNABIX_BOT_SERVICE_TOKEN`: bearer-token для `/api/v1/service/bot/*`.

## Запрещенные значения для staging и production

Перед деплоем проверь, что production env не содержит локальные placeholder credentials:

```text
SNABIX_BOT_SERVICE_TOKEN=change-me
SNABIX_BACKEND_SERVICE_TOKEN=change-me
SNABIX_BACKEND_SERVICE_TOKEN=replace-with-backend-service-token
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
DB_USERNAME=root
DB_PASSWORD=1234
DB_TEST_USERNAME=root
DB_TEST_PASSWORD=1234
```

Для staging и production нужны значения, сгенерированные отдельно для каждого окружения:

- отдельный `APP_KEY` через `php artisan key:generate --show`;
- отдельный пользователь PostgreSQL без `root` и с уникальным паролем из secret-хранилища;
- отдельный пользователь RabbitMQ без `guest/guest`;
- отдельный `SNABIX_BOT_SERVICE_TOKEN`, совпадающий только с bot `SNABIX_BACKEND_SERVICE_TOKEN`;
- отдельные SMTP credentials.

Автоматическая проверка production env:

```bash
PRODUCTION_ENV_FILE=/path/to/.env.production task secrets:production
```

CI запускает self-test guard:

```bash
php scripts/check-production-secrets.php --self-test
```

Если guard нашел placeholder в staging или production, секрет считается скомпрометированным: сгенерируй новое значение, обнови secret-хранилище, перезапусти сервисы и проверь логи доступа.

## Связка backend и bot

Backend:

```env
SNABIX_BOT_SERVICE_TOKEN=<generated-64-hex-token>
```

Bot:

```env
SNABIX_BACKEND_SERVICE_TOKEN=<same-generated-64-hex-token>
```

Bot отправляет:

```http
Authorization: Bearer <generated-64-hex-token>
```

Backend проверяет токен middleware-классом:

```text
App\Bot\Infrastructure\Middleware\EnsureBotServiceToken
```

## Генерация токенов

```bash
openssl rand -hex 32
```

Для каждого окружения нужен отдельный токен:

- local;
- staging;
- production.

Не используй Telegram bot token как backend service token.

## Ротация service token

1. Сгенерировать новый токен.
2. Обновить backend env.
3. Обновить bot env.
4. Перезапустить backend, если config закеширован.
5. Перезапустить bot.
6. Проверить `/health` в Telegram.
7. Удалить старый токен из secret-хранилища.

## Ротация SMTP

1. Создать новый пароль у SMTP-провайдера.
2. Обновить backend env.
3. Перезапустить queue worker.
4. Отправить тестовое письмо.
5. Проверить Mailpit или SMTP provider logs.

## Если секрет утек

1. Сразу отозвать секрет.
2. Создать новый.
3. Проверить логи доступа.
4. Убедиться, что секрет не остался в истории коммитов.
5. При необходимости переписать git history только после согласования.

## Проверка перед коммитом

```bash
git diff --cached
git status --short --ignored=no
```

Ищи подозрительные строки:

- `token=`;
- `password=`;
- `secret=`;
- `BOT_TOKEN`;
- `APP_KEY`;
- реальные SMTP credentials;
- дампы `.sql`, `.dump`, `.backup`.
