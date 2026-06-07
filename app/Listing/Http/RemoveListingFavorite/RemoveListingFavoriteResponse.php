<?php

declare(strict_types=1);

namespace App\Listing\Http\RemoveListingFavorite;

use App\Listing\Application\UseCases\RemoveListingFavorite\RemoveListingFavoriteOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RemoveListingFavoriteOutput
 */
class RemoveListingFavoriteResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
