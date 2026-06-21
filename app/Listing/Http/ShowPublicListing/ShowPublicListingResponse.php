<?php

declare(strict_types=1);

namespace App\Listing\Http\ShowPublicListing;

use App\Listing\Application\UseCases\ShowPublicListing\ShowPublicListingOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ShowPublicListingOutput
 */
class ShowPublicListingResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
