<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Listing\Application\Services\ListingInputNormalizer;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Services\ListingAttributeValueSynchronizer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class EloquentListingRepository implements ListingRepositoryInterface
{
    public function __construct(
        private ListingAttributeValueSynchronizer $listingAttributeValueSynchronizer,
        private ListingStatusTransitionPolicy $listingStatusTransitionPolicy,
        private ListingInputNormalizer $listingInputNormalizer,
    ) {}

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
    ): LengthAwarePaginator {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'media'])
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

    /**
     * @return LengthAwarePaginator<int, EloquentListing>
     */
    public function listPublicPublished(
        int $page = 1,
        int $perPage = 24,
    ): LengthAwarePaginator {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'media'])
            ->where('status', ListingStatus::PUBLISHED)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->latest('created_at')
            ->paginate(
                perPage: $perPage,
                pageName: 'page',
                page: $page,
            );
    }

    /**
     * @param  array<string, mixed>    $attributes
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function create(
        array $attributes,
        array $attributeValues = [],
    ): EloquentListing {
        /** @var EloquentListing $listing */
        $listing = DB::transaction(function () use ($attributes, $attributeValues): EloquentListing {
            $normalized = $this->listingInputNormalizer->normalizeForCreate($attributes);

            $listing    = EloquentListing::query()->create([
                ...$normalized->attributes,
                'slug'             => $this->generateUniqueSlug($normalized->title()),
                'published_at'     => null,
                'expires_at'       => null,
            ]);

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $normalized->category->id,
                attributeValues: $attributeValues,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
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
                'slug' => $this->generateUniqueSlug($normalized->title(), $listing->id),
            ]);
            $listing->save();

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $normalized->category->id,
                attributeValues: $attributeValues,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
        });

        return $updatedListing;
    }

    public function findOwnedByUser(string $listingId, string $userId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'media'])
            ->whereKey($listingId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findById(string $listingId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'media'])
            ->whereKey($listingId)
            ->first();
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

            return $listing->fresh(['category', 'attributeValues.attributeDefinition', 'media']) ?? $listing;
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

    private function generateUniqueSlug(
        string $title,
        ?string $ignoreId = null,
    ): string {
        $baseSlug  = Str::slug($title);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => ['Не удалось сформировать slug для объявления.'],
            ]);
        }

        $candidate = $baseSlug;
        $counter   = 2;

        while (
            EloquentListing::query()
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
