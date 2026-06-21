<?php

declare(strict_types=1);

namespace App\Shared\Http\Resources;

use Illuminate\Http\Request;

abstract class ItemOutputResource extends OutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->requireObjectArray(
            parent::toArray($request)['item'] ?? null,
            'item',
        );
    }
}
