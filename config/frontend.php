<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Support\FrontendDomainConfig;

$frontendUrl  = (string) env('FRONTEND_URL', 'http://localhost:3000');
$frontendUrls = env('FRONTEND_URLS');
$frontendUrls = is_string($frontendUrls) ? $frontendUrls : null;

return [
    'url'                => $frontendUrl,
    'urls'               => FrontendDomainConfig::urls($frontendUrl, $frontendUrls),
    'reset_password_url' => env('FRONTEND_RESET_PASSWORD_URL', rtrim($frontendUrl, '/') . '/reset-password'),
];
