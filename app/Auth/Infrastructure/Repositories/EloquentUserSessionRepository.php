<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Repositories;

use App\Auth\Domain\Contracts\UserSessionRepositoryInterface;
use App\Auth\Infrastructure\Models\EloquentSession;
use Illuminate\Database\Eloquent\Builder;

class EloquentUserSessionRepository implements UserSessionRepositoryInterface
{
    public function deleteAllForUser(string $userId): int
    {
        return $this->forUser($userId)->toBase()->delete();
    }

    public function deleteOtherForUser(string $userId, ?string $currentSessionId): int
    {
        $query = $this->forUser($userId);

        if ($currentSessionId !== null) {
            $query->where('id', '!=', $currentSessionId);
        }

        return $query->toBase()->delete();
    }

    public function deleteForUser(string $userId, string $sessionId): int
    {
        return $this->forUser($userId)
            ->where('id', $sessionId)
            ->toBase()
            ->delete();
    }

    /**
     * @return Builder<EloquentSession>
     */
    private function forUser(string $userId): Builder
    {
        return EloquentSession::query()
            ->where('user_id', $userId);
    }
}
