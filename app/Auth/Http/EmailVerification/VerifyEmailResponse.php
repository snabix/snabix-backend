<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin VerifyEmailOutput
 */
#[OA\Schema(
    schema: 'AuthVerifyEmailResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'verified', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Email успешно подтвержден'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class VerifyEmailResponse extends JsonResource
{
    /**
     * @return array{verified: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'verified' => (bool) data_get($this->resource, 'verified'),
            'message' => 'Email успешно подтвержден',
        ];
    }
}
