# SNABIX

- Платформа для размещения объявлений о продаже и товаров.

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
