<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Application\Support\NormalizedListingData;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;

interface ListingWriterInterface
{
    /**
     * @param array<array-key, mixed> $attributeValues
     */
    public function create(
        NormalizedListingData $data,
        array $attributeValues = [],
    ): EloquentListing;

    /**
     * @param array<array-key, mixed> $attributeValues
     */
    public function update(
        EloquentListing $listing,
        NormalizedListingData $data,
        array $attributeValues = [],
    ): EloquentListing;

    public function transitionStatus(
        EloquentListing $listing,
        ListingStatus $status,
        ?string $rejectionReason = null,
    ): EloquentListing;

    public function delete(EloquentListing $listing): void;
}
