<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListingMedia;

use App\Listing\Application\UseCases\DeleteListingMedia\DeleteListingMediaOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin DeleteListingMediaOutput
 */
class DeleteListingMediaResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
