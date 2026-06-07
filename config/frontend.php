<?php

declare(strict_types=1);

$frontendUrl = (string) env('FRONTEND_URL', 'http://localhost:3000');

return [
    'url'                => $frontendUrl,
    'reset_password_url' => env('FRONTEND_RESET_PASSWORD_URL', rtrim($frontendUrl, '/') . '/reset-password'),
];
