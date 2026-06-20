<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Infrastructure\Models\EloquentListing;

interface ListingReadRepositoryInterface
{
    public function findById(string $listingId): ?EloquentListing;
}
