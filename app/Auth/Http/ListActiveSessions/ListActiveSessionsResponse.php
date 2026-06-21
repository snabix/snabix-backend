<?php

declare(strict_types=1);

namespace App\Auth\Http\ListActiveSessions;

use App\Auth\Application\UseCases\ListActiveSessions\ListActiveSessionsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListActiveSessionsOutput
 */
class ListActiveSessionsResponse extends OutputResource
{
    /**
     * @return array{items: list<array<string, mixed>>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
