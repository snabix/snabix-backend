<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarHandler;
use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarInput;
use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use Illuminate\Http\Request;

class DeleteProfileAvatarController
{
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
