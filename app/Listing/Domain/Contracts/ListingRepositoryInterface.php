<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Pagination\LengthAwarePaginator;

interface ListingRepositoryInterface
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
        ?int $categoryId = null,
    ): LengthAwarePaginator;

    /**
     * @return LengthAwarePaginator<int, EloquentListing>
     */
    public function listPublicPublished(
        int $page = 1,
        int $perPage = 24,
    ): LengthAwarePaginator;

    /**
     * @param array<string, mixed>    $attributes
     * @param array<array-key, mixed> $attributeValues
     */
    public function create(
        array $attributes,
        array $attributeValues = [],
        bool $validateRequiredAttributes = true,
    ): EloquentListing;

    /**
     * @param array<string, mixed>    $attributes
     * @param array<array-key, mixed> $attributeValues
     */
    public function update(
        EloquentListing $listing,
        array $attributes,
        array $attributeValues = [],
        bool $validateRequiredAttributes = true,
    ): EloquentListing;

    public function findOwnedByUser(string $listingId, string $userId): ?EloquentListing;

    public function findById(string $listingId): ?EloquentListing;

    public function transitionStatus(EloquentListing $listing, ListingStatus $status): EloquentListing;

    public function delete(EloquentListing $listing): void;
}
