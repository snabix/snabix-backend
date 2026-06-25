# SNABIX

- Платформа для размещения объявлений о продаже и товаров.

## Локальная почта

В development-окружении SMTP-письма перехватывает Mailpit и не отправляет их во внешний интернет.

- SMTP внутри Docker-сети: `mailpit:1025`.
- Веб-интерфейс для просмотра писем: [http://localhost:8025](http://localhost:8025).
- Запуск сервиса: `docker compose up -d mailpit`.

Письма подтверждения email, восстановления пароля и пользовательские уведомления можно открыть прямо в интерфейсе Mailpit.
История писем хранится в Docker volume `mailpit_data` и сохраняется при перезапуске контейнера.

## Admin Commands

### Filament Shield

- Сгенерировать permissions и policies для admin-панели:

```bash
php artisan shield:generate --all --panel=admin
```

- Назначить `super_admin` существующему администратору:

```bash
php artisan shield:super-admin --panel=admin --user=<admin_id>
```

- Если нужен интерактивный выбор пользователя:

```bash
php artisan shield:super-admin --panel=admin
```

Важно:
- `shield:generate` не создает произвольные бизнес-роли вроде `manager` или `editor`, а генерирует permissions и policies для Filament entities.
- Роль `super_admin` назначается отдельно через `shield:super-admin`.
