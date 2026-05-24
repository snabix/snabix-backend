<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordHandler;
use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordInput;

class ForgotPasswordController
{
    public function __invoke(
        ForgotPasswordRequest $request,
        ForgotPasswordHandler $handler,
    ): ForgotPasswordResponse {
        $result = $handler->execute(ForgotPasswordInput::from($request->inputData()));

        return ForgotPasswordResponse::make($result);
    }
}
