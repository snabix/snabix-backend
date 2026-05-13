<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationHandler;
use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use OpenApi\Attributes as OA;

class ResendEmailVerificationController
{
    #[OA\Post(
        path: '/api/v1/auth/email-verification-notification',
        operationId: 'authResendEmailVerification',
        summary: 'Resend email verification link for current authenticated user',
        security: [['sanctumSession' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Verification email resent',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResendEmailVerificationResponse'),
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ],
    )]
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        ResendEmailVerificationRequest $request,
        ResendEmailVerificationHandler $handler,
    ): ResendEmailVerificationResponse {
        $result = $handler->execute(
            ResendEmailVerificationInput::from([
                'userId' => $request->authenticatedUserId(),
            ]),
        );

        return ResendEmailVerificationResponse::make($result);
    }
}
