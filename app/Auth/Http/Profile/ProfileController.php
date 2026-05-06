<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use OpenApi\Attributes as OA;

class ProfileController
{
    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'authProfile',
        summary: 'Get current authenticated user profile',
        security: [['sanctumBearer' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user profile',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthProfileResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        ProfileRequest $request,
        ProfileHandler $handler,
    ): ProfileResponse {
        $result = $handler->execute(
            ProfileInput::from([
                'userId' => $request->authenticatedUserId(),
            ]),
        );

        return ProfileResponse::make($result);
    }
}
