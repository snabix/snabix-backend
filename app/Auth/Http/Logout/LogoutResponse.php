<?php

declare(strict_types=1);

namespace App\Auth\Http\Logout;

use App\Auth\Application\UseCases\Logout\LogoutOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin LogoutOutput
 */
class LogoutResponse extends OutputResource
{
    /**
     * @return array{loggedOut: bool, message: string}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
