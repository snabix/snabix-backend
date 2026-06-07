<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAvatar;

use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarHandler;
use App\Auth\Application\UseCases\DeleteProfileAvatar\DeleteProfileAvatarInput;

class DeleteProfileAvatarController
{
    public function __invoke(
        DeleteProfileAvatarRequest $request,
        DeleteProfileAvatarHandler $handler,
    ): DeleteProfileAvatarResponse {
        $result = $handler->execute(DeleteProfileAvatarInput::from($request->inputData()));

        return DeleteProfileAvatarResponse::make($result);
    }
}
