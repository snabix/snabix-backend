<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpHandler;
use App\Auth\Application\UseCases\SignUp\SignUpInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Throwable;

class SignUpController
{
    /**
     * @throws NotFoundException|Throwable
     */
    public function __invoke(
        SignUpRequest $request,
        SignUpHandler $handler,
    ): SignUpResponse {
        $response = $handler->execute(SignUpInput::from($request->inputData()));

        return SignUpResponse::make($response);
    }
}
