<?php

declare(strict_types=1);

use App\Shared\Infrastructure\HealthChecks\RabbitMqHealthCheck;
use App\Shared\Infrastructure\HealthChecks\SystemResourcesHealthCheck;
use UKFast\HealthCheck\Checks\CacheHealthCheck;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\Checks\EnvHealthCheck;
use UKFast\HealthCheck\Checks\LogHealthCheck;
use UKFast\HealthCheck\Checks\MigrationUpToDateHealthCheck;
use UKFast\HealthCheck\Checks\RedisHealthCheck;

return [
    'base-path'                 => '',

    'route-paths'               => [
        'health' => '/health',
        'ping'   => '/ping',
    ],

    'checks'                    => [
        LogHealthCheck::class,
        DatabaseHealthCheck::class,
        RedisHealthCheck::class,
        CacheHealthCheck::class,
        MigrationUpToDateHealthCheck::class,
        EnvHealthCheck::class,
        SystemResourcesHealthCheck::class,
        RabbitMqHealthCheck::class,
    ],

    'middleware'                => [],

    'auth'                      => [
        'user'     => env('HEALTH_CHECK_USER'),
        'password' => env('HEALTH_CHECK_PASSWORD'),
    ],

    'route-name'                => 'healthcheck',

    'database'                  => [
        'connections' => ['default'],
    ],

    'required-env'              => [
        'APP_KEY',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'RABBITMQ_HOST',
        'RABBITMQ_PORT',
    ],

    'addresses'                 => [],

    'default-response-code'     => 200,

    'default-problem-http-code' => 500,

    'default-curl-timeout'      => 2.0,

    'x-service-checks'          => [],

    'cache'                     => [
        'stores' => [
            'array',
        ],
    ],

    'storage'                   => [
        'disks' => [
            'local',
        ],
    ],

    'package-security'          => [
        'exclude-dev' => false,
        'ignore'      => [],
    ],

    'scheduler'                 => [
        'cache-key'              => 'laravel-scheduler-health-check',
        'minutes-between-checks' => 5,
    ],

    'env-check-key'             => 'HEALTH_CHECK_ENV_DEFAULT_VALUE',
];
