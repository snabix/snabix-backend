<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListActiveSessions;

use Spatie\LaravelData\Data;

class ListActiveSessionsInput extends Data
{
    public function __construct(
        public string $userId,
        public ?string $currentSessionId,
    ) {}
}
