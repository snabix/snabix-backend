<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateSession;

use Spatie\LaravelData\Data;

class TerminateSessionInput extends Data
{
    public function __construct(
        public string $userId,
        public string $sessionId,
    ) {}
}
