<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailHandler;
use App\Auth\Application\UseCases\EmailVerification\VerifyEmailInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use OpenApi\Attributes as OA;

class VerifyEmailController
{
    #[OA\Get(
        path: '/api/v1/auth/verify-email',
        operationId: 'authVerifyEmail',
        summary: 'Verify user email by signed URL',
        tags: ['Auth'],
        parameters: [
            new OA\Parameter(
                name: 'user',
                description: 'User UUID',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
            new OA\Parameter(
                name: 'expires',
                description: 'Signed URL expiration timestamp',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
            new OA\Parameter(
                name: 'signature',
                description: 'Signed URL hash',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email successfully verified',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthVerifyEmailResponse'),
            ),
            new OA\Response(response: 403, description: 'Invalid or expired signed URL'),
        ],
    )]
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        VerifyEmailRequest $request,
        VerifyEmailHandler $handler,
    ): VerifyEmailResponse {
        $result = $handler->execute(
            VerifyEmailInput::from([
                'userId' => $request->query('user'),
            ]),
        );

        return VerifyEmailResponse::make($result);
    }
}
