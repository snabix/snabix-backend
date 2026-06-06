<?php

declare(strict_types=1);

namespace App\Listing\Http\AddListingFavorite;

use App\Listing\Application\UseCases\AddListingFavorite\AddListingFavoriteOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AddListingFavoriteOutput
 */
class AddListingFavoriteResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
