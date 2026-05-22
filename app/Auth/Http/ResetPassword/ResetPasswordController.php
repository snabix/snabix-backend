<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use App\Auth\Application\UseCases\ResetPassword\ResetPasswordHandler;
use App\Auth\Application\UseCases\ResetPassword\ResetPasswordInput;
use Illuminate\Validation\ValidationException;

class ResetPasswordController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        ResetPasswordRequest $request,
        ResetPasswordHandler $handler,
    ): ResetPasswordResponse {
        $result = $handler->execute(ResetPasswordInput::from($request->inputData()));

        return ResetPasswordResponse::make($result);
    }
}
