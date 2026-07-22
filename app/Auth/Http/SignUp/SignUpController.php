<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpHandler;
use App\Auth\Application\UseCases\SignUp\SignUpInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Dedoc\Scramble\Attributes\HeaderParameter;
use Throwable;

#[HeaderParameter(
    'Idempotency-Key',
    description: 'Необязательный уникальный ключ повтора create-запроса, 8-128 символов.',
    required: false,
    type: 'string',
    example: 'signup-019f4f54-19c2-7f39-a778-e328b85cd690',
)]
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
