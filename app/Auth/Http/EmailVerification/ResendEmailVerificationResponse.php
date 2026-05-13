<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ResendEmailVerificationOutput
 */
#[OA\Schema(
    schema: 'AuthResendEmailVerificationResponse',
    properties: [
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'sent', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Код подтверждения отправлен повторно.'),
                new OA\Property(property: 'cooldownSeconds', type: 'integer', example: 60),
            ],
            type: 'object',
        ),
    ],
    type: 'object',
)]
class ResendEmailVerificationResponse extends JsonResource
{
    /**
     * @return array{sent: bool, message: string, cooldownSeconds: int}
     */
    public function toArray(Request $request): array
    {
        return [
            'sent'            => $this->sent,
            'message'         => $this->message,
            'cooldownSeconds' => $this->cooldownSeconds,
        ];
    }
}
