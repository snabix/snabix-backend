<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarHandler;
use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarInput;

class UpdateProfileAvatarController
{
    public function __invoke(
        UpdateProfileAvatarRequest $request,
        UpdateProfileAvatarHandler $handler,
        ProfileHandler $profileHandler,
    ): ProfileResponse {
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $handler->execute(UpdateProfileAvatarInput::from([
            'userId' => $userId,
            'avatar' => $request->file('avatar'),
        ]));

        return ProfileResponse::make(
            $profileHandler->execute(ProfileInput::from(['userId' => $userId])),
        );
    }
}
