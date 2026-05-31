<?php

declare(strict_types=1);

namespace App\Listing\Http\ListFavoriteListings;

use App\Listing\Application\UseCases\ListFavoriteListings\ListFavoriteListingsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListFavoriteListingsOutput
 */
class ListFavoriteListingsResponse extends JsonResource
{
    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->items,
            'meta'  => $this->meta,
        ];
    }
}
