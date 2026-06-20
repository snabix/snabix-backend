<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Queries;

use App\Listing\Domain\Contracts\OwnedListingQueryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class OwnedListingQuery implements OwnedListingQueryInterface
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
    ): LengthAwarePaginator {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->where('user_id', $userId)
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->when($type !== null, fn($query) => $query->where('type', $type))
            ->when($categoryId !== null, fn($query) => $query->where('category_id', $categoryId))
            ->latest('updated_at')
            ->paginate(
                perPage: $perPage,
                pageName: 'page',
                page: $page,
            );
    }
}
