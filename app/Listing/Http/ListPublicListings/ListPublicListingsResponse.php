<?php

declare(strict_types=1);

namespace App\Listing\Http\ListPublicListings;

use App\Listing\Application\UseCases\ListPublicListings\ListPublicListingsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListPublicListingsOutput
 */
class ListPublicListingsResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->items,
            'meta'  => $this->meta,
        ];
    }
}
