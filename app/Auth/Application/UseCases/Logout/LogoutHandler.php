<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\Logout;

use App\Auth\Domain\Events\UserLoggedOut;
use App\Auth\Infrastructure\Models\EloquentUser;
use Laravel\Sanctum\PersonalAccessToken;

readonly class LogoutHandler
{
    public function execute(LogoutInput $data): LogoutOutput
    {
        if ($data->tokenId !== null) {
            PersonalAccessToken::query()
                ->whereKey($data->tokenId)
                ->where('tokenable_id', $data->userId)
                ->where('tokenable_type', EloquentUser::class)
                ->delete();
        }

        event(new UserLoggedOut(
            userId: $data->userId,
            tokenId: $data->tokenId !== null ? (string) $data->tokenId : null,
        ));

        return LogoutOutput::from([
            'loggedOut' => true,
            'message' => 'Вы успешно вышли из аккаунта.',
        ]);
    }
}
