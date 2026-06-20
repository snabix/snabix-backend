<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Pagination\LengthAwarePaginator;

interface OwnedListingQueryInterface
{
    /**
     * @return LengthAwarePaginator<int, EloquentListing>
     */
    public function listOwnedByUser(
        string $userId,
        int $page = 1,
        int $perPage = 12,
        ?ListingStatus $status = null,
        ?int $type = null,
        ?string $categoryId = null,
    ): LengthAwarePaginator;
}
