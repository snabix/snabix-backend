<?php

declare(strict_types=1);

return [
    'staging_chunk_size'  => 250,
    'promotion_chunk_size'=> 250,
    'stale_after_seconds' => 3600,
    'lock_seconds'        => 300,
    'lock_wait_seconds'   => 5,
    'budgets'             => [
        'peak_memory_bytes' => 96 * 1024 * 1024,
        'duration_seconds'  => 30,
        'query_count'       => 60,
    ],
];
