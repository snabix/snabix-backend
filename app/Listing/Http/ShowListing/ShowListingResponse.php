<?php

declare(strict_types=1);

namespace App\Listing\Http\ShowListing;

use App\Listing\Application\UseCases\ShowListing\ShowListingOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ShowListingOutput
 */
class ShowListingResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
