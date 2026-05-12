<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpHandler;
use App\Auth\Application\UseCases\SignUp\SignUpInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use OpenApi\Attributes as OA;
use Throwable;

class SignUpController
{
    #[OA\Post(
        path: '/api/v1/auth/sign-up',
        operationId: 'authSignUp',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthSignUpRequest'),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User successfully registered',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthSignUpResponse'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    /**
     * @throws NotFoundException|Throwable
     */
    public function __invoke(
        SignUpRequest $request,
        SignUpHandler $handler,
    ): SignUpResponse {
        $data     = SignUpInput::from([
            'firstName'            => $request->string('firstName')->toString(),
            'lastName'             => $request->string('lastName')->toString(),
            'phoneNumber'          => $request->string('phoneNumber')->toString(),
            'email'                => $request->string('email')->toString(),
            'password'             => $request->string('password')->toString(),
            'passwordConfirmation' => $request->string('passwordConfirmation')->toString(),
        ]);

        $response = $handler->execute($data);

        return SignUpResponse::make($response);
    }
}
