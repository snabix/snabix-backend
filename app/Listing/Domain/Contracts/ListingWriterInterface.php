<?php

declare(strict_types=1);

namespace App\Listing\Domain\Contracts;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;

interface ListingWriterInterface
{
    /**
     * @param array<string, mixed>    $attributes
     * @param array<array-key, mixed> $attributeValues
     */
    public function create(
        array $attributes,
        array $attributeValues = [],
    ): EloquentListing;

    /**
     * @param array<string, mixed>    $attributes
     * @param array<array-key, mixed> $attributeValues
     */
    public function update(
        EloquentListing $listing,
        array $attributes,
        array $attributeValues = [],
    ): EloquentListing;

    public function transitionStatus(
        EloquentListing $listing,
        ListingStatus $status,
    ): EloquentListing;

    public function delete(EloquentListing $listing): void;
}
