<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin SignUpOutput
 */
#[OA\Schema(
    schema: 'AuthSignUpResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'userId', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class SignUpResponse extends JsonResource
{
    /**
     * @return array{userId: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->userId,
        ];
    }
}
