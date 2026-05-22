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
        $result = $handler->execute(VerifyEmailInput::from($request->inputData()));

        return VerifyEmailResponse::make($result);
    }
}
