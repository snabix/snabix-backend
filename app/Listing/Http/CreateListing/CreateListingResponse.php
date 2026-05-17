<?php

declare(strict_types=1);

namespace App\Listing\Http\CreateListing;

use App\Listing\Application\UseCases\CreateListing\CreateListingOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CreateListingOutput
 */
class CreateListingResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
