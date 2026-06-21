<?php

declare(strict_types=1);

namespace App\Listing\Http\ListFavoriteListings;

use App\Listing\Application\UseCases\ListFavoriteListings\ListFavoriteListingsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListFavoriteListingsOutput
 */
class ListFavoriteListingsResponse extends OutputResource
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
