<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

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
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            ProfileInput::from([
                'userId' => $userId,
            ]),
        );

        return ProfileResponse::make($result);
    }
}
