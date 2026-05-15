<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Application\UseCases\UpdateListing\UpdateListingOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UpdateListingOutput
 */
class UpdateListingResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
