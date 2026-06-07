<?php

declare(strict_types=1);

namespace App\Auth\Http\UpdateProfileAvatar;

use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarHandler;
use App\Auth\Application\UseCases\UpdateProfileAvatar\UpdateProfileAvatarInput;

class UpdateProfileAvatarController
{
    public function __invoke(
        UpdateProfileAvatarRequest $request,
        UpdateProfileAvatarHandler $handler,
    ): UpdateProfileAvatarResponse {
        $result = $handler->execute(UpdateProfileAvatarInput::from($request->inputData()));

        return UpdateProfileAvatarResponse::make($result);
    }
}
