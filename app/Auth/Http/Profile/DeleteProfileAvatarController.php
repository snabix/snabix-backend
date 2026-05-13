<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarHandler;
use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarInput;
use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DeleteProfileAvatarController
{
    #[OA\Delete(
        path: '/api/v1/auth/me/avatar',
        operationId: 'authDeleteProfileAvatar',
        summary: 'Delete current authenticated user avatar',
        security: [['sanctumSession' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Avatar successfully deleted',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthProfileResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function __invoke(
        Request                    $request,
        DeleteProfileAvatarHandler $handler,
        ProfileHandler             $profileHandler,
    ): ProfileResponse {
        $user     = $request->user();
        $userId   = is_object($user) && (is_string($user->getAuthIdentifier()) || is_int($user->getAuthIdentifier()))
            ? (string) $user->getAuthIdentifier()
            : '';

        $handler->execute(
            DeleteProfileAvatarInput::from([
                'userId' => $userId,
            ]),
        );

        $response = $profileHandler->execute(
            ProfileInput::from(['userId' => $userId]),
        );

        return ProfileResponse::make($response);
    }
}
