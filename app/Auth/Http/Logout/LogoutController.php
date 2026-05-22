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
        $result = $handler->execute(
            LogoutInput::from([
                'userId' => $request->userId(),
            ]),
        );

        return LogoutResponse::make($result);
    }
}
