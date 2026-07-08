# Секреты backend

Документ описывает backend-секреты Snabix и правила безопасной работы с ними.

## Главное правило

Реальные секреты нельзя коммитить.

Можно хранить в репозитории:

- `.env.example` с placeholder-значениями;
- названия переменных окружения;
- инструкции по генерации и ротации.

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

## Связка backend и bot

Backend:

```env
SNABIX_BOT_SERVICE_TOKEN=long-random-token
```

Bot:

```env
SNABIX_BACKEND_SERVICE_TOKEN=long-random-token
```

Bot отправляет:

```http
Authorization: Bearer long-random-token
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
