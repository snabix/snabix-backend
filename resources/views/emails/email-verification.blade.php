<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
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
                            Email Verification
                        </h1>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333333;">

                        <p style="font-size:16px; margin-bottom:15px;">
                            Hello, <strong>{{ $username }}</strong>
                        </p>

                        <p style="font-size:14px; margin-bottom:25px;">
                            Thank you for registering. Please confirm your email address by clicking the button below:
                        </p>

                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:25px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ $verificationUrl }}"
                                       style="
                                            display:inline-block;
                                            padding:12px 24px;
                                            background-color:#2563eb;
                                            color:#ffffff;
                                            text-decoration:none;
                                            border-radius:6px;
                                            font-size:14px;
                                       ">
                                        Verify Email
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="font-size:13px; color:#666;">
                            If the button doesn’t work, copy and paste this link into your browser:
                        </p>

                        <p style="word-break:break-all; font-size:12px; color:#2563eb;">
                            {{ $verificationUrl }}
                        </p>

                        <p style="font-size:13px; color:#999; margin-top:30px;">
                            This link will expire in 60 minutes.
                        </p>

                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f9fafb; padding:15px; text-align:center; font-size:12px; color:#999;">
                        © {{ date('Y') }} Your Application. All rights reserved.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
