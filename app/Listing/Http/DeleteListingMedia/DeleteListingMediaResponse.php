<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListingMedia;

use App\Listing\Application\UseCases\DeleteListingMedia\DeleteListingMediaOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeleteListingMediaOutput
 */
class DeleteListingMediaResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
