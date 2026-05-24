<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use App\Auth\Application\UseCases\SignIn\SignInHandler;
use App\Auth\Application\UseCases\SignIn\SignInInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;

class SignInController
{
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        SignInRequest $request,
        SignInHandler $handler,
    ): SignInResponse {
        $result = $handler->execute(SignInInput::from($request->inputData()));

        return SignInResponse::make($result);
    }
}
