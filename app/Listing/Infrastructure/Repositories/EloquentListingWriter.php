<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Listing\Application\Services\ListingInputNormalizer;
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
        private ListingInputNormalizer $listingInputNormalizer,
        private ListingSlugGenerator $listingSlugGenerator,
    ) {}

    /**
     * @param  array<string, mixed>    $attributes
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function create(array $attributes, array $attributeValues = []): EloquentListing
    {
        /** @var EloquentListing $listing */
        $listing = DB::transaction(function () use ($attributes, $attributeValues): EloquentListing {
            $normalized = $this->listingInputNormalizer->normalizeForCreate($attributes);

            $listing    = EloquentListing::query()->create([
                ...$normalized->attributes,
                'slug'         => $this->listingSlugGenerator->generate($normalized->title()),
                'published_at' => null,
                'expires_at'   => null,
            ]);

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $normalized->category->id,
                attributeValues: $attributeValues,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'orderedMedia']) ?? $listing;
        });

        return $listing;
    }

    /**
     * @param  array<string, mixed>    $attributes
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function update(
        EloquentListing $listing,
        array $attributes,
        array $attributeValues = [],
    ): EloquentListing {
        /** @var EloquentListing $updatedListing */
        $updatedListing = DB::transaction(function () use ($listing, $attributes, $attributeValues): EloquentListing {
            $normalized = $this->listingInputNormalizer->normalizeForUpdate($listing, $attributes);

            $this->listingStatusTransitionPolicy->assertCanTransition(
                $listing->status,
                $normalized->status(),
            );

            $listing->fill([
                ...$normalized->attributes,
                'slug' => $this->listingSlugGenerator->generate($normalized->title(), $listing->id),
            ]);
            $listing->save();

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $normalized->category->id,
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

            $listing->forceFill([
                'status'           => $status,
                'rejection_reason' => $status === ListingStatus::PENDING_REVIEW
                    ? null
                    : $listing->rejection_reason,
            ]);
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
