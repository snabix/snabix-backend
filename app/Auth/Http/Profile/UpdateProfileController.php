<?php

declare(strict_types=1);

namespace App\Auth\Http\Profile;

use App\Auth\Application\UseCases\UpdateProfile\UpdateProfileHandler;
use App\Auth\Application\UseCases\UpdateProfile\UpdateProfileInput;
use App\Auth\Infrastructure\Exceptions\NotFoundException;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class UpdateProfileController
{
    #[OA\Patch(
        path: '/api/v1/auth/me',
        operationId: 'authUpdateProfile',
        summary: 'Update current authenticated user profile',
        security: [['sanctumSession' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AuthUpdateProfileRequest'),
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile successfully updated',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthProfileResponse'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    /**
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function __invoke(
        UpdateProfileRequest $request,
        UpdateProfileHandler $handler,
    ): ProfileResponse {
        $result = $handler->execute(
            UpdateProfileInput::from([
                'userId'      => $request->authenticatedUserId(),
                'firstName'   => $request->string('firstName')->toString(),
                'lastName'    => $request->string('lastName')->toString(),
                'email'       => $request->string('email')->toString(),
                'phoneNumber' => $request->input('phoneNumber'),
            ]),
        );

        return ProfileResponse::make($result);
    }
}
