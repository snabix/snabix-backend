<?php

declare(strict_types=1);

return [
    'max_response_bytes' => (int) env('CATALOG_IMPORT_MAX_RESPONSE_BYTES', 5 * 1024 * 1024),
    'fixture_roots'      => [
        storage_path('app/imports/categories'),
        base_path('tests/Fixtures/catalog'),
    ],
    'user_agent'         => env('CATALOG_IMPORT_USER_AGENT', 'SnabixCatalogImporter/1.0'),
    'sources'            => [
        'prom.ua' => [
            'version'                       => env('CATALOG_IMPORT_PROM_VERSION', 'prom-dom-v1'),
            'url'                           => env('CATALOG_IMPORT_PROM_URL', 'https://prom.ua/consumer-goods'),
            'allowed_hosts'                 => ['prom.ua'],
            'require_explicit_external_ids' => true,
            'network_enabled'               => (bool) env('CATALOG_IMPORT_PROM_NETWORK_ENABLED', false),
            'rights_reference'              => env('CATALOG_IMPORT_PROM_RIGHTS_REFERENCE'),
        ],
    ],
];
