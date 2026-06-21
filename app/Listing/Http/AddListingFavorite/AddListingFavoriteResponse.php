<?php

declare(strict_types=1);

namespace App\Listing\Http\AddListingFavorite;

use App\Listing\Application\UseCases\AddListingFavorite\AddListingFavoriteOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin AddListingFavoriteOutput
 */
class AddListingFavoriteResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
