<?php

declare(strict_types=1);

namespace App\Auth\Http\ChangePassword;

use App\Auth\Application\UseCases\ChangePassword\ChangePasswordHandler;
use App\Auth\Application\UseCases\ChangePassword\ChangePasswordInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;

class ChangePasswordController
{
    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function __invoke(
        ChangePasswordRequest $request,
        ChangePasswordHandler $handler,
    ): ChangePasswordResponse {
        $result = $handler->execute(ChangePasswordInput::from($request->inputData()));

        return ChangePasswordResponse::make($result);
    }
}
