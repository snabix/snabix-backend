<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationHandler;
use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;

class ResendEmailVerificationController
{
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        ResendEmailVerificationRequest $request,
        ResendEmailVerificationHandler $handler,
    ): ResendEmailVerificationResponse {
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            ResendEmailVerificationInput::from([
                'userId' => $userId,
            ]),
        );

        return ResendEmailVerificationResponse::make($result);
    }
}
