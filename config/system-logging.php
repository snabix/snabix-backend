<?php

declare(strict_types=1);

return [
    'enabled'                  => env('SYSTEM_LOGGING_ENABLED', true),
    'http_requests_enabled'    => env('SYSTEM_LOGGING_HTTP_REQUESTS_ENABLED', true),
    'log_safe_requests'        => env('SYSTEM_LOGGING_HTTP_SAFE_REQUESTS', false),
    'log_unsafe_requests'      => env('SYSTEM_LOGGING_HTTP_UNSAFE_REQUESTS', true),
    'log_client_errors'        => env('SYSTEM_LOGGING_HTTP_CLIENT_ERRORS', true),
    'log_server_errors'        => env('SYSTEM_LOGGING_HTTP_SERVER_ERRORS', true),
    'slow_request_threshold_ms'=> (int) env('SYSTEM_LOGGING_HTTP_SLOW_REQUEST_MS', 1500),
    'retention_days'           => (int) env('SYSTEM_LOGGING_RETENTION_DAYS', 30),
    'ignored_path_prefixes'    => [
        '_ignition',
        'livewire',
        'up',
        'health',
        'ping',
        'docs',
        'api/documentation',
        'storage',
    ],
];
