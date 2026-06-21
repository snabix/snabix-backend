<?php

declare(strict_types=1);

namespace App\Auth\Http\SignIn;

use App\Auth\Application\UseCases\SignIn\SignInOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin SignInOutput
 */
class SignInResponse extends OutputResource
{
    /**
     * @return array{userId: string}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
