<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Domain\Contracts\TokenCreatorInterface;
use RuntimeException;

class SanctumTokenCreatorService implements TokenCreatorInterface
{
    public function create(
        string $userId,
        string $tokenName,
    ): string {
        $user = EloquentUser::query()->find($userId);

        if (!$user) {
            throw new RuntimeException(
                "Пользователь не найден!",
            );
        }

        $token = $user->createToken($tokenName);

        return $token->plainTextToken;
    }
}
