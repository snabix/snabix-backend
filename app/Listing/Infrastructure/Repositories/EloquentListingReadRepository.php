<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;

final readonly class EloquentListingReadRepository implements ListingReadRepositoryInterface
{
    public function findById(string $listingId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->whereKey($listingId)
            ->first();
    }
}
