<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Listing\Application\Normalizers\ListingModerationNormalizer;
use App\Listing\Application\Support\NormalizedListingData;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Services\ListingAttributeValueSynchronizer;
use App\Listing\Infrastructure\Services\ListingSlugGenerator;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class EloquentListingWriter implements ListingWriterInterface
{
    public function __construct(
        private ListingAttributeValueSynchronizer $listingAttributeValueSynchronizer,
        private ListingStatusTransitionPolicy $listingStatusTransitionPolicy,
        private ListingModerationNormalizer $listingModerationNormalizer,
        private ListingSlugGenerator $listingSlugGenerator,
    ) {}

    /**
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function create(NormalizedListingData $data, array $attributeValues = []): EloquentListing
    {
        /** @var EloquentListing $listing */
        $listing = DB::transaction(function () use ($data, $attributeValues): EloquentListing {
            $listing    = EloquentListing::query()->create([
                ...$data->attributes,
                'slug' => $this->listingSlugGenerator->generate($data->title()),
            ]);

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $data->category->id,
                attributeValues: $attributeValues,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
        });

        return $listing;
    }

    /**
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function update(
        EloquentListing $listing,
        NormalizedListingData $data,
        array $attributeValues = [],
    ): EloquentListing {
        /** @var EloquentListing $updatedListing */
        $updatedListing = DB::transaction(function () use ($listing, $data, $attributeValues): EloquentListing {
            $listing->fill([
                ...$data->attributes,
                'slug' => $this->listingSlugGenerator->generate($data->title(), $listing->id),
            ]);
            $listing->save();

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $data->category->id,
                attributeValues: $attributeValues,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
        });

        return $updatedListing;
    }

    /**
     * @throws Throwable
     */
    public function transitionStatus(
        EloquentListing $listing,
        ListingStatus $status,
    ): EloquentListing {
        /** @var EloquentListing $updatedListing */
        $updatedListing = DB::transaction(function () use ($listing, $status): EloquentListing {
            $this->listingStatusTransitionPolicy->assertCanTransition($listing->status, $status);

            $listing->forceFill(
                $this->listingModerationNormalizer->statusTransitionAttributes($listing, $status),
            );
            $listing->save();

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
        });

        return $updatedListing;
    }

    /**
     * @throws Throwable
     */
    public function delete(EloquentListing $listing): void
    {
        DB::transaction(function () use ($listing): void {
            $listing->attributeValues()->delete();
            $listing->delete();
        });
    }
}
