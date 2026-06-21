<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Application\UseCases\ListListings\ListListingsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListListingsOutput
 */
class ListListingsResponse extends OutputResource
{
    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, int>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
