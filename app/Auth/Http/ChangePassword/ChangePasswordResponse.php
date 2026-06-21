<?php

declare(strict_types=1);

namespace App\Auth\Http\ChangePassword;

use App\Auth\Application\UseCases\ChangePassword\ChangePasswordOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ChangePasswordOutput
 */
class ChangePasswordResponse extends OutputResource
{
    /**
     * @return array{changed: bool, message: string}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
