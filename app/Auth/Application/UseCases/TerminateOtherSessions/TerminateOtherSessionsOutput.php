<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateOtherSessions;

use Spatie\LaravelData\Data;

class TerminateOtherSessionsOutput extends Data
{
    public function __construct(
        public bool $terminated,
        public int $terminatedCount,
    ) {}
}
