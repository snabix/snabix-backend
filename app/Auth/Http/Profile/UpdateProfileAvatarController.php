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
        $userId = $request->userId();

        $handler->execute(UpdateProfileAvatarInput::from($request->inputData()));

        return ProfileResponse::make(
            $profileHandler->execute(ProfileInput::from(['userId' => $userId])),
        );
    }
}
