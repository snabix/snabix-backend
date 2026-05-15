<?php

declare(strict_types=1);

namespace App\Listing\Http\ShowListing;

use App\Listing\Application\UseCases\ShowListing\ShowListingOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShowListingOutput
 */
class ShowListingResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
