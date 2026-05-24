<?php

declare(strict_types=1);

namespace App\Auth\Http\ShowProfile;

use App\Auth\Application\UseCases\Profile\ProfileHandler;
use App\Auth\Application\UseCases\Profile\ProfileInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;

class ProfileController
{
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        ProfileRequest $request,
        ProfileHandler $handler,
    ): ProfileResponse {
        $result = $handler->execute(ProfileInput::from($request->inputData()));

        return ProfileResponse::make($result);
    }
}
