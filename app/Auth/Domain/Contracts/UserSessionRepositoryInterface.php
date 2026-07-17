<?php

declare(strict_types=1);

namespace App\Auth\Domain\Contracts;

interface UserSessionRepositoryInterface
{
    public function deleteAllForUser(string $userId): int;

    public function deleteOtherForUser(string $userId, ?string $currentSessionId): int;

    public function deleteForUser(string $userId, string $sessionId): int;
}
