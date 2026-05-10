<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarHandler;
use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarInput;
use OpenApi\Attributes as OA;

class UpdateProfileAvatarController
{
    #[OA\Post(
        path: '/api/v1/auth/me/avatar',
        operationId: 'authUpdateProfileAvatar',
        summary: 'Upload or replace current authenticated user avatar',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/AuthUpdateProfileAvatarRequest'),
            ),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar successfully updated',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthProfileResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function __invoke(
        UpdateProfileAvatarRequest $request,
        UpdateProfileAvatarHandler $handler,
        ProfileHandler $profileHandler,
    ): ProfileResponse {
        $userId = $request->authenticatedUserId();

        $handler->execute(UpdateProfileAvatarInput::from([
            'userId' => $userId,
            'avatar' => $request->file('avatar'),
        ]));

        return ProfileResponse::make(
            $profileHandler->execute(ProfileInput::from(['userId' => $userId])),
        );
    }
}
