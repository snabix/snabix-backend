<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Collection;

interface ListingRepositoryInterface
{
    /**
     * @return Collection<int, EloquentListing>
     */
    public function listOwnedByUser(string $userId): Collection;

    /**
     * @return Collection<int, EloquentListing>
     */
    public function listPublicPublished(int $limit = 24): Collection;

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

    public function delete(EloquentListing $listing): void;
}
