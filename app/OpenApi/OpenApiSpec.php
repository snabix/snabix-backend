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
    securityScheme: 'sanctumBearer',
    type: 'http',
    description: 'Передавайте токен Sanctum в заголовке Authorization: Bearer {token}.',
    bearerFormat: 'Token',
    scheme: 'bearer',
)]
final class OpenApiSpec {}
