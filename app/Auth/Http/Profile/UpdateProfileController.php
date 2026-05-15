<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

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
    ): ProfileResponse {
        $request->validated();
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            UpdateProfileInput::from([
                'userId'      => $userId,
                'firstName'   => $request->string('firstName')->toString(),
                'lastName'    => $request->string('lastName')->toString(),
                'email'       => $request->string('email')->toString(),
                'phoneNumber' => $request->filled('phoneNumber')
                    ? $request->string('phoneNumber')->toString()
                    : null,
            ]),
        );

        return ProfileResponse::make($result);
    }
}
