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
        $data     = SignUpInput::from($request->validated());

        $response = $handler->execute($data);

        return SignUpResponse::make($response);
    }
}
