<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateOtherSessions;

use Spatie\LaravelData\Data;

class TerminateOtherSessionsInput extends Data
{
    public function __construct(
        public string $userId,
        public ?string $currentSessionId,
    ) {}
}
