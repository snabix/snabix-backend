<?php

declare(strict_types=1);

namespace App\Auth\Http\ResendEmailVerification;

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
        $result = $handler->execute(
            ResendEmailVerificationInput::from([
                'userId' => $request->userId(),
            ]),
        );

        return ResendEmailVerificationResponse::make($result);
    }
}
