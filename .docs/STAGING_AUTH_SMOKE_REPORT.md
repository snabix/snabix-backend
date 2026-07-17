# Staging auth/session/CSRF smoke report

Документ является шаблоном и журналом фактической проверки auth flow на HTTPS
staging. Он не должен содержать пароли, reset tokens, cookie values, содержимое
`X-XSRF-TOKEN` или персональные данные.

## Статус

- Audit task: `P0-AUTH-001`.
- Дата подготовки: `2026-07-17`.
- Фактический staging smoke: `PENDING`.
- Причина: staging frontend/API origins и безопасный тестовый доступ отсутствуют
  в workspace.
- Условие закрытия: все обязательные сценарии ниже имеют `PASS`, а cookie
  attributes заполнены по фактическому HTTPS-сеансу.

## Окружение

| Параметр | Фактическое значение |
|---|---|
| Frontend origin | `PENDING` |
| API origin | `PENDING` |
| Проверенный frontend commit | `PENDING` |
| Проверенный backend commit | `PENDING` |
| Browser и версия | `PENDING` |
| Время проверки, UTC | `PENDING` |
| Исполнитель | `PENDING` |
| Защищенная ссылка на deploy/CI run | `PENDING` |

Почтовый ящик и учетная запись должны быть отдельными staging-ресурсами. В отчет
записывается только обезличенный идентификатор сценария, без email и пароля.

## Environment matrix

| Переменная | Ожидаемое правило | Фактическое значение |
|---|---|---|
| `NEXT_PUBLIC_API_URL` | HTTPS API URL с `/api/v1` | `PENDING` |
| `APP_URL` | HTTPS API origin | `PENDING` |
| `FRONTEND_URL` | Основной HTTPS frontend origin | `PENDING` |
| `FRONTEND_URLS` | Точный список разрешенных origins | `PENDING` |
| `SANCTUM_STATEFUL_DOMAINS` | Frontend hosts без протокола | `PENDING` |
| `SESSION_DRIVER` | `database` | `PENDING` |
| `SESSION_DOMAIN` | Общий parent domain или обоснованный API host | `PENDING` |
| `SESSION_SECURE_COOKIE` | `true` | `PENDING` |
| `SESSION_HTTP_ONLY` | `true` | `PENDING` |
| `SESSION_SAME_SITE` | `lax` для same-site или обоснованный `none` | `PENDING` |
| `SESSION_LIFETIME` | Согласованный rolling idle lifetime | `PENDING` |
| `FRONTEND_RESET_PASSWORD_URL` | `${FRONTEND_URL}/reset-password` | `PENDING` |
| CORS credentials | `true`, без wildcard origin | `PENDING` |

## Cookie attributes

Значения cookie не записывать.

| Cookie | Domain | Path | Secure | HttpOnly | SameSite | Result |
|---|---|---|---|---|---|---|
| `XSRF-TOKEN` | `PENDING` | `PENDING` | `PENDING` | `PENDING` | `PENDING` | `PENDING` |
| Laravel session | `PENDING` | `PENDING` | `PENDING` | `PENDING` | `PENDING` | `PENDING` |

## Сценарии

| ID | Проверка | Ожидаемый результат | Статус | Evidence без секретов |
|---|---|---|---|---|
| AUTH-01 | Sign-up | Аккаунт создан, session установлена | `PENDING` | `PENDING` |
| AUTH-02 | Sign-in | CSRF cookie получена до unsafe request, вход успешен | `PENDING` | `PENDING` |
| AUTH-03 | Refresh private route | Авторизация сохраняется после reload | `PENDING` | `PENDING` |
| AUTH-04 | Unsafe authenticated request | Есть `X-XSRF-TOKEN`, нет `419` | `PENDING` | `PENDING` |
| AUTH-05 | Terminate selected/other session | Отозванная сессия получает `401`, текущая работает | `PENDING` | `PENDING` |
| AUTH-06 | Change password | Session ID/CSRF rotated, остальные сессии получают `401` | `PENDING` | `PENDING` |
| AUTH-07 | Forgot/reset email | Ссылка открывает `/reset-password` | `PENDING` | `PENDING` |
| AUTH-08 | Reset password | Все старые сессии получают `401`, новый пароль работает | `PENDING` | `PENDING` |
| AUTH-09 | Logout | Текущая сессия завершена | `PENDING` | `PENDING` |
| AUTH-10 | Forced `401` | UI предлагает войти без технической ошибки | `PENDING` | `PENDING` |
| AUTH-11 | Forced `419` | UI предлагает войти без `CSRF token mismatch` | `PENDING` | `PENDING` |
| AUTH-12 | Additional allowed origins | Flow повторен для каждого `FRONTEND_URLS` origin | `PENDING` | `PENDING` |

## Локальные автоматические проверки

На этапе подготовки `2026-07-17` feature/unit-тестами подтверждено:

- reset URL использует существующий frontend route `/reset-password`;
- смена пароля удаляет остальные sessions пользователя;
- восстановление пароля удаляет все sessions пользователя;
- sessions других пользователей не затрагиваются;
- существующие list/terminate active sessions сценарии остаются рабочими.

Автоматические проверки не подтверждают browser cookie attributes, реальный
CORS между поддоменами, доставку reset email или поведение staging proxy/CDN.

## Итог

Результат остается `PENDING`, пока таблицы окружения, cookie attributes и
сценариев не заполнены данными фактического HTTPS staging smoke. После проверки
здесь фиксируются итог `PASS` или `FAIL`, найденные отклонения и ссылки на задачи
исправления.
