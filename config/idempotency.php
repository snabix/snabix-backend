<?php

declare(strict_types=1);

return [
    'retention_hours' => (int) env('IDEMPOTENCY_RETENTION_HOURS', 24),
];
