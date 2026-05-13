<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение email</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f4f4;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#111827; padding:20px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:20px;">
                            Подтверждение email
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333333;">

                        <p style="font-size:16px; margin-bottom:15px;">
                            Здравствуйте, <strong>{{ $username }}</strong>
                        </p>

                        <p style="font-size:14px; margin-bottom:25px;">
                            Чтобы подтвердить адрес электронной почты, введите этот код в личном кабинете:
                        </p>

                        <div style="margin:0 auto 24px; max-width:260px; padding:16px 24px; border-radius:14px; background:#f8fafc; border:1px solid #e5e7eb; text-align:center;">
                            <div style="font-size:32px; letter-spacing:8px; font-weight:700; color:#111827;">
                                {{ $verificationCode }}
                            </div>
                        </div>

                        <p style="font-size:13px; color:#666; margin-bottom:10px;">
                            Код действует {{ $expiresInMinutes }} минут. Если вы не запрашивали подтверждение, просто проигнорируйте это письмо.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
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
