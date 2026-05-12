<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailHandler;
use App\Auth\Application\UseCases\EmailVerification\VerifyEmailInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use App\Shared\Infrastructure\Services\FrontendUrlBuilder;
use Illuminate\Http\RedirectResponse;
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
                response: 302,
                description: 'Redirect to frontend after email verification',
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
        FrontendUrlBuilder $frontendUrlBuilder,
    ): RedirectResponse {
        $userId                  = $request->string('user')->toString();
        $verificationRedirectUrl = config('frontend.email_verification_redirect_url');

        $handler->execute(
            VerifyEmailInput::from([
                'userId' => $userId,
            ]),
        );

        return redirect()->away($frontendUrlBuilder->build(
            is_string($verificationRedirectUrl) ? $verificationRedirectUrl : '/',
            [
                'verified' => 1,
                'user'     => $userId,
            ],
        ));
    }
}
