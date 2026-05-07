
## Про проект

`Snabix Backend` построен на `Laravel 12`, `PHP 8.3`, `Sanctum`, `Filament 5`, `L5 Swagger` и health-check пакете.
Основной стиль кода сейчас ближе к layered / application-first архитектуре:

- HTTP слой: `app/*/Http/*`
- Application/use case слой: `app/*/Application/UseCases/*`
- Domain слой: `app/*/Domain/*`
- Infrastructure слой: `app/*/Infrastructure/*`
- Shared cross-cutting код: `app/Shared/*`

Для новых возможностей старайся продолжать именно этот стиль, а не смешивать контроллеры, бизнес-логику и Eloquent в одном месте.

## Архитектурные правила

- Каждый API-метод должен иметь отдельный `Controller` или invokable controller в `app/*/Http/*`.
- Входные данные валидируются через `FormRequest`.
- Данные в application-слой передаются через `Input` DTO.
- Результат use case возвращается через `Output` DTO.
- Ответ API отдаётся через `JsonResource`-класс в `Response`.
- Бизнес-логика должна жить в `Application/UseCases`, а не в контроллере.
- Если действие значимое для аудита, предпочтителен `event(...)` + listener, а не прямой вызов логгера из каждого handler.
- Техническое логирование HTTP-запросов должно идти через middleware / инфраструктурный слой.
- Если меняется доменная сущность пользователя, сначала смотри на `app/Auth/Domain/Entities/User.php`, а не редактируй логику только на уровне Eloquent.

## Auth модуль

Текущий Auth модуль уже использует единый паттерн:

- `Request` -> `Input` -> `Handler` -> `Output` -> `Response`
- Репозиторий пользователя: `App\Auth\Domain\Contracts\UserRepositoryInterface`
- Eloquent реализация: `App\Auth\Infrastructure\Repositories\EloquentUserRepository`
- События домена и аудита: `app/Auth/Domain/Events`

При добавлении нового auth API:

- создай request/response классы;
- опиши endpoint OpenAPI-атрибутами;
- добавь маршрут в `routes/api.php`;
- если действие важно для истории системы, публикуй событие, которое затем логируется listener-ом;
- если нужен email-флоу, используй существующий подход с `Job` + `MailSender` + blade view.

## Логирование

В проекте есть два типа логирования:

- HTTP activity logging: через `App\Shared\Infrastructure\Middleware\LogRequestActivity`
- бизнес-аудит: через события, реализующие `App\Shared\Domain\Contracts\LoggableEvent`, и listener `PersistLoggableEventListener`

Не размазывай технические логи по handler-классам, если это можно решить middleware, listener-ом, observer-ом или event-driven способом.

## Тесты

Обязательное правило: каждый новый API-метод покрывается feature-тестами.

Минимум для нового endpoint:

- happy path тест;
- тест на валидацию или ошибочный сценарий;
- если endpoint меняет состояние системы, проверка базы;
- если endpoint инициирует побочный эффект, проверка очереди / почты / логов.

Текущие feature-тесты опираются на `tests/Feature/FeatureTestCase.php`.
Не возвращайся к полному ручному `RefreshDatabase` в каждом тесте без необходимости.
Если для тестов нужны стабильные системные данные, добавляй их через `database/seeders/TestDatabaseSeeder.php`.

## Стиль изменений

- Сохраняй строгую типизацию `declare(strict_types=1);`
- Следуй текущему неймингу директорий и классов.
- Новые shared-инструменты складывай в `app/Shared`.
- Не добавляй случайные helper-функции вне существующей структуры.
- Если меняешь API, синхронизируй Swagger-документацию и тесты.
- Если меняешь поведение авторизации, проверь сценарии `sign-in`, `sign-up`, `verify-email`, `logout`, `me`.

## Админка и виджеты

- Админ-панель собирается через `Filament\AdminPanelProvider`.
- Виджеты состояния системы лежат в `app/Shared/Filament/Widgets`.
- Blade-шаблоны виджетов лежат в `resources/views/filament/widgets`.
- Для health widgets сохраняй акцент на читаемости, статусах, процентах использования и быстрых визуальных сигналах.

## Что проверять перед завершением

- endpoint добавлен в `routes/api.php`;
- request/response/use case созданы и подключены;
- swagger generation не сломана;
- feature-тесты на новый API написаны;
- если есть новый аудит-сценарий, он попадает в `system_logs`;
- если изменение влияет на UI админки, проверь blade/widget код на понятность и аккуратность.
