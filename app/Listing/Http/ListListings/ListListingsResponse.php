<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Application\UseCases\ListListings\ListListingsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListListingsOutput
 */
class ListListingsResponse extends JsonResource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return $this->items;
    }
}
