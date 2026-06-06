<?php

declare(strict_types=1);

namespace App\Listing\Http\SetMainListingMedia;

use App\Listing\Application\UseCases\SetMainListingMedia\SetMainListingMediaOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SetMainListingMediaOutput
 */
class SetMainListingMediaResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
