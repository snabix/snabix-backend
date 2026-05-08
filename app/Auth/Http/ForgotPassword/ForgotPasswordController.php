<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordHandler;
use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordInput;
use OpenApi\Attributes as OA;

class ForgotPasswordController
{
    #[OA\Post(
        path: '/api/v1/auth/forgot-password',
        operationId: 'authForgotPassword',
        summary: 'Request password reset instructions',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthForgotPasswordRequest'),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset instructions were accepted for processing',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthForgotPasswordResponse'),
            ),
        ],
    )]
    public function __invoke(
        ForgotPasswordRequest $request,
        ForgotPasswordHandler $handler,
    ): ForgotPasswordResponse {
        $result = $handler->execute(
            ForgotPasswordInput::from([
                'email' => $request->string('email')->toString(),
            ]),
        );

        return ForgotPasswordResponse::make($result);
    }
}
