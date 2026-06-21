<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ForgotPasswordOutput
 */
class ForgotPasswordResponse extends OutputResource
{
    /**
     * @return array{sent: bool, message: string}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
