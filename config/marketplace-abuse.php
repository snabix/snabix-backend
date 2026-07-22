<?php

declare(strict_types=1);

return [
    'limits' => [
        'catalog_read'         => [
            'ip' => ['attempts' => 1200, 'decay_seconds' => 60],
        ],
        'location_read'        => [
            'ip' => ['attempts' => 1200, 'decay_seconds' => 60],
        ],
        'listing_read'         => [
            'ip' => ['attempts' => 600, 'decay_seconds' => 60],
        ],
        'news_read'            => [
            'ip' => ['attempts' => 600, 'decay_seconds' => 60],
        ],
        'review_read'          => [
            'ip' => ['attempts' => 600, 'decay_seconds' => 60],
        ],
        'account_listing_read' => [
            'user' => ['attempts' => 180, 'decay_seconds' => 60],
            'ip'   => ['attempts' => 1800, 'decay_seconds' => 60],
        ],
        'listing_create'       => [
            'user' => ['attempts' => 10, 'decay_seconds' => 3600],
            'ip'   => ['attempts' => 100, 'decay_seconds' => 3600],
        ],
        'listing_write'        => [
            'user' => ['attempts' => 60, 'decay_seconds' => 60],
            'ip'   => ['attempts' => 600, 'decay_seconds' => 60],
        ],
        'listing_submit'       => [
            'user' => ['attempts' => 20, 'decay_seconds' => 3600],
            'ip'   => ['attempts' => 200, 'decay_seconds' => 3600],
        ],
        'listing_media_write'  => [
            'user' => ['attempts' => 60, 'decay_seconds' => 3600],
            'ip'   => ['attempts' => 600, 'decay_seconds' => 3600],
        ],
        'favorite_write'       => [
            'user' => ['attempts' => 120, 'decay_seconds' => 60],
            'ip'   => ['attempts' => 1200, 'decay_seconds' => 60],
        ],
        'review_create'        => [
            'user' => ['attempts' => 5, 'decay_seconds' => 3600],
            'ip'   => ['attempts' => 100, 'decay_seconds' => 3600],
        ],
    ],
];
