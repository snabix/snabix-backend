<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ваши данные аккаунта SNABIX</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:100%; background:#ffffff; border-radius:10px; overflow:hidden;">
                <tr>
                    <td style="background:#111827; padding:22px 24px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:20px; line-height:1.35;">
                            Ваши данные аккаунта SNABIX
                        </h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px; color:#333333;">
                        <p style="font-size:16px; line-height:1.5; margin:0 0 16px;">
                            Здравствуйте, <strong>{{ $accountLabel }}</strong>.
                        </p>

                        <p style="font-size:14px; line-height:1.6; margin:0 0 18px;">
                            Вы запросили копию персональных данных аккаунта. Мы приложили JSON-файл с текущей информацией профиля на момент запроса: {{ $requestedAt }}.
                        </p>

                        <div style="padding:16px; border-radius:14px; background:#f9fafb; border:1px solid #e5e7eb;">
                            <p style="font-size:14px; line-height:1.6; color:#111827; margin:0; font-weight:700;">
                                Вложение: snabix-profile-data.json
                            </p>
                            <p style="font-size:13px; line-height:1.6; color:#6b7280; margin:8px 0 0;">
                                Файл содержит профиль, контакты и адреса. Пароль не экспортируется: в системе хранится только защищенный хеш.
                            </p>
                        </div>

                        <p style="font-size:13px; line-height:1.6; color:#6b7280; margin:24px 0 0;">
                            Если вы не запрашивали эти данные, проверьте активные сессии и смените пароль.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="background:#f9fafb; padding:15px; text-align:center; font-size:12px; color:#999;">
                        © {{ date('Y') }} Snabix. Все права защищены.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
