<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Application\Services\ListingInputNormalizer;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Services\ListingAttributeValueSynchronizer;
use Illuminate\Database\Eloquent\Builder;
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
    ): LengthAwarePaginator {
        $query = EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia', 'region', 'city'])
            ->where('status', ListingStatus::PUBLISHED)
            ->when($type !== null, fn($query) => $query->where('type', $type))
            ->when($minPrice !== null, fn($query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn($query) => $query->where('price', '<=', $maxPrice))
            ->when($regionId !== null, fn($query) => $query->where('region_id', $regionId))
            ->when($cityId !== null, fn($query) => $query->where('city_id', $cityId));

        $this->applyCategoryFilter($query, $categoryId);
        $this->applyLocationSearchFilter($query, 'region', $regionQuery);
        $this->applyLocationSearchFilter($query, 'city', $cityQuery);
        $this->applyPublicSort($query, $sort);

        return $query
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
                'slug' => $this->generateUniqueSlug($normalized->title(), $listing->id),
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

    public function findOwnedByUser(string $listingId, string $userId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->whereKey($listingId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findById(string $listingId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->whereKey($listingId)
            ->first();
    }

    public function findPublicPublishedById(string $listingId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia'])
            ->whereKey($listingId)
            ->where('status', ListingStatus::PUBLISHED)
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

    /**
     * @param Builder<EloquentListing> $query
     */
    private function applyLocationSearchFilter(Builder $query, string $relation, ?string $search): void
    {
        $term      = trim((string) $search);

        if ($term === '') {
            return;
        }

        $likeValue = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        $query->where(function (Builder $locationQuery) use ($relation, $likeValue): void {
            $locationQuery->whereHas(
                $relation,
                function (Builder $relatedQuery) use ($relation, $likeValue): void {
                    $relatedQuery->where('name', 'ilike', $likeValue)
                        ->orWhere('label', 'ilike', $likeValue);

                    if ($relation === 'region') {
                        $relatedQuery->orWhere('fullname', 'ilike', $likeValue);
                    }
                },
            );

            if ($relation === 'region') {
                $locationQuery
                    ->orWhere('address_snapshot->region->name', 'ilike', $likeValue)
                    ->orWhere('address_snapshot->region->fullName', 'ilike', $likeValue)
                    ->orWhere('address_snapshot->region->label', 'ilike', $likeValue);

                return;
            }

            $locationQuery
                ->orWhere('address_snapshot->city->name', 'ilike', $likeValue)
                ->orWhere('address_snapshot->city->label', 'ilike', $likeValue);
        });
    }

    /**
     * @param Builder<EloquentListing> $query
     */
    private function applyCategoryFilter(
        Builder $query,
        ?string $categoryId,
    ): void {
        if ($categoryId === null) {
            return;
        }

        $category = EloquentCategory::query()->find($categoryId);

        if ($category === null) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $query) use ($category): void {
            $query
                ->where('category_id', $category->id)
                ->orWhereHas(
                    'category',
                    fn(Builder $query): Builder => $query->where('path', 'like', $category->path . '/%'),
                );
        });
    }

    /**
     * @param Builder<EloquentListing> $query
     */
    private function applyPublicSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest'     => $query
                ->orderBy('published_at')
                ->orderBy('created_at'),
            'price_asc'  => $query
                ->orderByRaw('price asc nulls last')
                ->orderByDesc('published_at'),
            'price_desc' => $query
                ->orderByRaw('price desc nulls last')
                ->orderByDesc('published_at'),
            'popular'    => $query
                ->orderByDesc('views_count')
                ->orderByDesc('published_at'),
            default      => $query
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->latest('created_at'),
        };
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
