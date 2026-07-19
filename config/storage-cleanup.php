<?php

declare(strict_types=1);

return [
    'enabled' => env('STORAGE_CLEANUP_ENABLED', true),

    'entries' => [
        'logs'                 => [
            'enabled'                  => env('STORAGE_CLEANUP_LOGS_ENABLED', true),
            'path'                     => storage_path('logs'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_LOGS_RETENTION_DAYS', 14),
            'patterns'                 => ['*.log', '*.gz', '*.json'],
            'delete_empty_directories' => false,
        ],

        'api_docs'             => [
            'enabled'                  => env('STORAGE_CLEANUP_API_DOCS_ENABLED', true),
            'path'                     => storage_path('api-docs'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_API_DOCS_RETENTION_DAYS', 30),
            'patterns'                 => ['*'],
            'delete_empty_directories' => true,
        ],

        'private_temp_uploads' => [
            'enabled'                  => env('STORAGE_CLEANUP_PRIVATE_TEMP_UPLOADS_ENABLED', true),
            'path'                     => storage_path('app/private'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_TEMP_UPLOADS_RETENTION_DAYS', 2),
            'patterns'                 => [
                'filament-media-temp/*',
                'filament-category-icons-temp/*',
                'livewire-tmp/*',
                env('MEDIA_STAGING_PREFIX', 'media-staging') . '/*/*',
            ],
            'delete_empty_directories' => true,
        ],

        'public_temp_uploads'  => [
            'enabled'                  => env('STORAGE_CLEANUP_PUBLIC_TEMP_UPLOADS_ENABLED', true),
            'path'                     => storage_path('app/public/livewire-tmp'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_TEMP_UPLOADS_RETENTION_DAYS', 2),
            'patterns'                 => ['*'],
            'delete_empty_directories' => true,
        ],

        'media_library_temp'   => [
            'enabled'                  => env('STORAGE_CLEANUP_MEDIA_LIBRARY_TEMP_ENABLED', true),
            'path'                     => storage_path('media-library/temp'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_MEDIA_LIBRARY_TEMP_RETENTION_DAYS', 2),
            'patterns'                 => ['*'],
            'delete_empty_directories' => true,
        ],

        'debugbar'             => [
            'enabled'                  => env('STORAGE_CLEANUP_DEBUGBAR_ENABLED', true),
            'path'                     => storage_path('debugbar'),
            'retention_days'           => (int) env('STORAGE_CLEANUP_DEBUGBAR_RETENTION_DAYS', 7),
            'patterns'                 => ['*'],
            'delete_empty_directories' => true,
        ],
    ],
];
