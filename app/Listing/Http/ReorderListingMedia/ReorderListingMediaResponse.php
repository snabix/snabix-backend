<?php

declare(strict_types=1);

namespace App\Listing\Http\ReorderListingMedia;

use App\Listing\Application\UseCases\ReorderListingMedia\ReorderListingMediaOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReorderListingMediaOutput
 */
class ReorderListingMediaResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
