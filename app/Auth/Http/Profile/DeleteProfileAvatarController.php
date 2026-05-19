<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarHandler;
use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarInput;
use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;

class DeleteProfileAvatarController
{
    public function __invoke(
        DeleteProfileAvatarRequest $request,
        DeleteProfileAvatarHandler $handler,
        ProfileHandler $profileHandler,
    ): ProfileResponse {
        $userId   = $request->userId();

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
