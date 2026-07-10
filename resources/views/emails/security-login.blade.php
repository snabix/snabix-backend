<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выполнен вход в аккаунт</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:100%; background:#ffffff; border-radius:10px; overflow:hidden;">
                <tr>
                    <td style="background:#111827; padding:22px 24px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:20px; line-height:1.35;">
                            Ваша учетная запись SNABIX — успешный вход
                        </h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px; color:#333333;">
                        <p style="font-size:16px; line-height:1.5; margin:0 0 16px;">
                            Здравствуйте, <strong>{{ $accountLabel }}</strong>.
                        </p>

                        <p style="font-size:14px; line-height:1.6; margin:0 0 22px;">
                            {{ $body }}
                        </p>

                        <p style="font-size:14px; line-height:1.6; margin:0 0 14px;">
                            Детали входа:
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; background:#f9fafb;">
                            <tr>
                                <td style="padding:13px 16px; width:38%; color:#6b7280; font-size:13px; border-bottom:1px solid #e5e7eb;">Расположение</td>
                                <td style="padding:13px 16px; color:#111827; font-size:14px; font-weight:600; border-bottom:1px solid #e5e7eb;">{{ $details['location'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px; width:38%; color:#6b7280; font-size:13px; border-bottom:1px solid #e5e7eb;">Устройство</td>
                                <td style="padding:13px 16px; color:#111827; font-size:14px; font-weight:600; border-bottom:1px solid #e5e7eb;">{{ $details['device'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px; width:38%; color:#6b7280; font-size:13px; border-bottom:1px solid #e5e7eb;">Браузер</td>
                                <td style="padding:13px 16px; color:#111827; font-size:14px; font-weight:600; border-bottom:1px solid #e5e7eb;">{{ $details['browser'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px; width:38%; color:#6b7280; font-size:13px; border-bottom:1px solid #e5e7eb;">IP-адрес</td>
                                <td style="padding:13px 16px; color:#111827; font-size:14px; font-weight:600; border-bottom:1px solid #e5e7eb;">{{ $details['ipAddress'] }}</td>
                            </tr>
                            <tr>
                                <td style="padding:13px 16px; width:38%; color:#6b7280; font-size:13px;">Время входа</td>
                                <td style="padding:13px 16px; color:#111827; font-size:14px; font-weight:600;">{{ $details['signedInAt'] }}</td>
                            </tr>
                        </table>

                        <p style="font-size:14px; line-height:1.6; margin:0 0 22px;">
                            Если это вы выполнили вход, дополнительных действий не требуется. Мы отправили это письмо, чтобы вы могли быстро заметить подозрительную активность.
                        </p>

                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 24px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $sessionsUrl }}"
                                       style="display:inline-block; padding:12px 22px; background-color:#2563eb; color:#ffffff; text-decoration:none; border-radius:8px; font-size:14px; font-weight:700;">
                                        Проверить активные сессии
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <div style="padding:16px; border-radius:14px; background:#fff7ed; border:1px solid #fed7aa;">
                            <p style="font-size:13px; line-height:1.6; color:#9a3412; margin:0 0 8px; font-weight:700;">
                                Если это были не вы:
                            </p>
                            <ol style="font-size:13px; line-height:1.6; color:#7c2d12; margin:0; padding-left:18px;">
                                <li>Откройте настройки безопасности и завершите неизвестные сессии.</li>
                                <li>Смените пароль от аккаунта.</li>
                                <li>Проверьте email и телефон в профиле.</li>
                            </ol>
                        </div>
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
