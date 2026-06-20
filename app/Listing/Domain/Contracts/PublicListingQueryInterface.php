<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Pagination\LengthAwarePaginator;

interface PublicListingQueryInterface
{
    /**
     * @return LengthAwarePaginator<int, EloquentListing>
     */
    public function listPublicPublished(
        int $page = 1,
        int $perPage = 15,
        ?string $categoryId = null,
        ?int $type = null,
        ?int $minPrice = null,
        ?int $maxPrice = null,
        ?int $regionId = null,
        ?int $cityId = null,
        ?string $regionQuery = null,
        ?string $cityQuery = null,
        string $sort = 'newest',
    ): LengthAwarePaginator;

    public function findPublicPublishedById(string $listingId): ?EloquentListing;
}
