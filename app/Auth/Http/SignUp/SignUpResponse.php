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
                new OA\Property(property: 'token', type: 'string', example: '1|sanctum-token'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class SignUpResponse extends JsonResource
{
    /**
     * @return array{token: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
