<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use App\Auth\Application\UseCases\ResetPassword\ResetPasswordHandler;
use App\Auth\Application\UseCases\ResetPassword\ResetPasswordInput;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ResetPasswordController
{
    #[OA\Post(
        path: '/api/v1/auth/reset-password',
        operationId: 'authResetPassword',
        summary: 'Reset password using a previously issued token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthResetPasswordRequest'),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password successfully changed',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResetPasswordResponse'),
            ),
            new OA\Response(response: 422, description: 'Invalid token or payload'),
        ],
    )]
    /**
     * @throws ValidationException
     */
    public function __invoke(
        ResetPasswordRequest $request,
        ResetPasswordHandler $handler,
    ): ResetPasswordResponse {
        $result = $handler->execute(
            ResetPasswordInput::from([
                'email' => $request->string('email')->toString(),
                'token' => $request->string('token')->toString(),
                'password' => $request->string('password')->toString(),
            ]),
        );

        return ResetPasswordResponse::make($result);
    }
}
