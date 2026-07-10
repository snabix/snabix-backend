<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Support\FrontendDomainConfig;

$frontendUrl  = env('FRONTEND_URL', 'http://localhost:3000');
$frontendUrl  = is_string($frontendUrl) ? $frontendUrl : null;
$frontendUrls = env('FRONTEND_URLS');
$frontendUrls = is_string($frontendUrls) ? $frontendUrls : null;

$frontendUrls = FrontendDomainConfig::urls(
    $frontendUrl,
    $frontendUrls,
    [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
    ],
);

return [
    'paths'                    => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods'          => ['*'],

    'allowed_origins'          => $frontendUrls,

    'allowed_origins_patterns' => [],

    'allowed_headers'          => ['*'],

    'exposed_headers'          => [],

    'max_age'                  => 0,

    'supports_credentials'     => true,
];
