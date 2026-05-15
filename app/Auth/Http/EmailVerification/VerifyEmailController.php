<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailHandler;
use App\Auth\Application\UseCases\EmailVerification\VerifyEmailInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;

class VerifyEmailController
{
    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function __invoke(
        VerifyEmailRequest $request,
        VerifyEmailHandler $handler,
    ): VerifyEmailResponse {
        $request->validated();
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            VerifyEmailInput::from([
                'userId' => $userId,
                'code'   => $request->string('code')->toString(),
            ]),
        );

        return VerifyEmailResponse::make($result);
    }
}
