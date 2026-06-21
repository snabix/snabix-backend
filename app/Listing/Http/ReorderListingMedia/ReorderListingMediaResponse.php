<?php

declare(strict_types=1);

namespace App\Listing\Http\ReorderListingMedia;

use App\Listing\Application\UseCases\ReorderListingMedia\ReorderListingMediaOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ReorderListingMediaOutput
 */
class ReorderListingMediaResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
