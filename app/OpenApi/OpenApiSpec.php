<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        version: '1.0.0',
        description: 'Документация API для авторизации, профиля и административных сценариев Snabix.',
        title: 'Snabix API',
    ),
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumSession',
    type: 'apiKey',
    description: 'Для first-party SPA используется cookie-based сессия Laravel Sanctum с CSRF-защитой.',
    name: 'snabix_session',
    in: 'cookie',
)]
final class OpenApiSpec {}
