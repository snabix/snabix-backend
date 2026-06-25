<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Events\ListingFavorited;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingFavorite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class ListingFavoriteService
{
    public function add(
        string $userId,
        string $listingId,
    ): EloquentListing {
        $listing = $this->findPublishedListing($listingId);

        $favorite = EloquentListingFavorite::query()->firstOrCreate([
            'user_id' => $userId,
            'listing_id' => $listing->id,
        ]);

        if ($favorite->wasRecentlyCreated && $listing->user_id !== $userId) {
            event(new ListingFavorited($listing, $userId));
        }

        return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
    }

    public function remove(
        string $userId,
        string $listingId,
    ): EloquentListing {
        $listing = $this->findPublishedListing($listingId);

        EloquentListingFavorite::query()
            ->where('user_id', $userId)
            ->where('listing_id', $listing->id)
            ->delete();

        return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
    }

    /**
     * @return LengthAwarePaginator<int, EloquentListing>
     */
    public function list(
        string $userId,
        int $page = 1,
        int $perPage = 12,
    ): LengthAwarePaginator {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->where('status', ListingStatus::PUBLISHED)
            ->whereHas(
                'favorites',
                fn ($query) => $query->where('user_id', $userId),
            )
            ->orderByDesc(
                EloquentListingFavorite::query()
                    ->select('created_at')
                    ->whereColumn('listing_favorites.listing_id', 'listings.id')
                    ->where('user_id', $userId)
                    ->limit(1),
            )
            ->paginate(
                perPage: $perPage,
                pageName: 'page',
                page: $page,
            );
    }

    private function findPublishedListing(
        string $listingId,
    ): EloquentListing {
        $listing = EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->whereKey($listingId)
            ->where('status', ListingStatus::PUBLISHED)
            ->first();

        if (! $listing instanceof EloquentListing) {
            throw (new ModelNotFoundException)->setModel(EloquentListing::class, [$listingId]);
        }

        return $listing;
    }
}
