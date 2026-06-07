<?php

declare(strict_types=1);

namespace App\Auth\Http\ListActiveSessions;

use App\Auth\Application\UseCases\ListActiveSessions\ListActiveSessionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListActiveSessionsOutput
 */
class ListActiveSessionsResponse extends JsonResource
{
    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->items,
        ];
    }
}
