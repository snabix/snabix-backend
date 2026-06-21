<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin SignUpOutput
 */
class SignUpResponse extends OutputResource
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
