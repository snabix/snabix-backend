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
                new OA\Property(property: 'alreadyVerified', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Email успешно подтвержден.'),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class VerifyEmailResponse extends JsonResource
{
    /**
     * @return array{verified: bool, alreadyVerified: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        $message = data_get($this->resource, 'message');

        return [
            'verified'        => (bool) data_get($this->resource, 'verified'),
            'alreadyVerified' => (bool) data_get($this->resource, 'alreadyVerified'),
            'message'         => is_string($message) ? $message : 'Email успешно подтвержден.',
        ];
    }
}
