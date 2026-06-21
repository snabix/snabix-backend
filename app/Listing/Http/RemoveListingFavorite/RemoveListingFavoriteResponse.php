<?php

declare(strict_types=1);

namespace App\Listing\Http\RemoveListingFavorite;

use App\Listing\Application\UseCases\RemoveListingFavorite\RemoveListingFavoriteOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin RemoveListingFavoriteOutput
 */
class RemoveListingFavoriteResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
