<?php

declare(strict_types=1);

namespace App\Auth\Http\UpdateProfile;

use App\Auth\Application\UseCases\UpdateProfile\UpdateProfileHandler;
use App\Auth\Application\UseCases\UpdateProfile\UpdateProfileInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;

class UpdateProfileController
{
    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function __invoke(
        UpdateProfileRequest $request,
        UpdateProfileHandler $handler,
    ): UpdateProfileResponse {
        $result = $handler->execute(UpdateProfileInput::from($request->inputData()));

        return UpdateProfileResponse::make($result);
    }
}
