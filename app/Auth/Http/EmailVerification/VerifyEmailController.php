<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailHandler;
use App\Auth\Application\UseCases\EmailVerification\VerifyEmailInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class VerifyEmailController
{
    #[OA\Post(
        path: '/api/v1/auth/verify-email',
        operationId: 'authVerifyEmail',
        summary: 'Verify current authenticated user email by code',
        security: [['sanctumSession' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthVerifyEmailRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email verified successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthVerifyEmailResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Invalid verification code'),
        ],
    )]
    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function __invoke(
        VerifyEmailRequest $request,
        VerifyEmailHandler $handler,
    ): VerifyEmailResponse {
        $result = $handler->execute(
            VerifyEmailInput::from([
                'userId' => $request->authenticatedUserId(),
                'code'   => $request->string('code')->toString(),
            ]),
        );

        return VerifyEmailResponse::make($result);
    }
}
