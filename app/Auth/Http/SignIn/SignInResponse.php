<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use App\Auth\Application\UseCases\SignIn\SignInOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin SignInOutput
 */
#[OA\Schema(
    schema: 'AuthSignInResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'token', type: 'string', example: '2|sanctum-token'),
                new OA\Property(property: 'userId', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class SignInResponse extends JsonResource
{
    /**
     * @return array{token: string, userId: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'userId' => $this->userId,
        ];
    }
}
