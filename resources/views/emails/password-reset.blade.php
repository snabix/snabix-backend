<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">
                <tr>
                    <td style="background:#111827; padding:20px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:20px;">Восстановление пароля</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px; color:#333333;">
                        <p style="font-size:16px; margin-bottom:15px;">
                            Здравствуйте, <strong>{{ $username }}</strong>
                        </p>

                        <p style="font-size:14px; margin-bottom:25px;">
                            Мы получили запрос на смену пароля. Перейдите по ссылке ниже, чтобы задать новый пароль.
                        </p>

                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:25px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $resetUrl }}"
                                       style="display:inline-block; padding:12px 24px; background-color:#2563eb; color:#ffffff; text-decoration:none; border-radius:6px; font-size:14px;">
                                        Сменить пароль
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size:13px; color:#666;">Если кнопка не работает, откройте ссылку вручную:</p>
                        <p style="word-break:break-all; font-size:12px; color:#2563eb;">{{ $resetUrl }}</p>
                        <p style="font-size:13px; color:#999; margin-top:30px;">Ссылка действует ограниченное время.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
