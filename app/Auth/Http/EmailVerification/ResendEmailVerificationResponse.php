<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ResendEmailVerificationOutput
 */
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
