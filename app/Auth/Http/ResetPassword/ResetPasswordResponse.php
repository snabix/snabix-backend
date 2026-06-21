<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use App\Auth\Application\UseCases\ResetPassword\ResetPasswordOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ResetPasswordOutput
 */
class ResetPasswordResponse extends OutputResource
{
    /**
     * @return array{reset: bool, message: string}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
