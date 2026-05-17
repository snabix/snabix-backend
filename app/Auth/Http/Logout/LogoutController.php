<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Application\UseCases\Logout\LogoutHandler;
use App\Auth\Application\UseCases\Logout\LogoutInput;

class LogoutController
{
    public function __invoke(
        LogoutRequest $request,
        LogoutHandler $handler,
    ): LogoutResponse {
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            LogoutInput::from([
                'userId' => $userId,
            ]),
        );

        return LogoutResponse::make($result);
    }
}
