<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use App\Auth\Application\UseCases\SignIn\SignInHandler;
use App\Auth\Application\UseCases\SignIn\SignInInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use OpenApi\Attributes as OA;

class SignInController
{
    #[OA\Post(
        path: '/api/v1/auth/sign-in',
        operationId: 'authSignIn',
        summary: 'Authenticate user and issue Sanctum token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthSignInRequest'),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User successfully authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthSignInResponse'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    /**
     * @throws NotFoundException
     */
    public function __invoke(
        SignInRequest $request,
        SignInHandler $handler,
    ): SignInResponse {
        $data   = SignInInput::from(
            $request->validated(),
        );

        $result = $handler->execute($data);

        return SignInResponse::make($result);
    }
}
