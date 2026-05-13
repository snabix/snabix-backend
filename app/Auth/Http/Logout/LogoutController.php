<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Application\UseCases\Logout\LogoutHandler;
use App\Auth\Application\UseCases\Logout\LogoutInput;
use OpenApi\Attributes as OA;

class LogoutController
{
    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'authLogout',
        summary: 'Logout current authenticated user from web session',
        security: [['sanctumSession' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User successfully logged out',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthLogoutResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function __invoke(
        LogoutRequest $request,
        LogoutHandler $handler,
    ): LogoutResponse {
        $result = $handler->execute(
            LogoutInput::from([
                'userId' => $request->authenticatedUserId(),
            ]),
        );

        return LogoutResponse::make($result);
    }
}
