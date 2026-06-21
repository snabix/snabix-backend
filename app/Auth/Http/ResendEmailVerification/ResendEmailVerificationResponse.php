<?php

declare(strict_types=1);

namespace App\Auth\Http\ResendEmailVerification;

use App\Auth\Application\UseCases\ResendEmailVerification\ResendEmailVerificationOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ResendEmailVerificationOutput
 */
class ResendEmailVerificationResponse extends OutputResource
{
    /**
     * @return array{sent: bool, message: string, cooldownSeconds: int}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
