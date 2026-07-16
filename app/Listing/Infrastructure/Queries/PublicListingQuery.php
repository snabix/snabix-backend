<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Queries;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Contracts\PublicListingQueryInterface;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class PublicListingQuery implements PublicListingQueryInterface
{
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
        ?bool $isNegotiable = null,
        string $sort = 'newest',
    ): LengthAwarePaginator {
        $query = EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia', 'region', 'city', 'user'])
            ->where('status', ListingStatus::PUBLISHED)
            ->when($type !== null, fn($query) => $query->where('type', $type))
            ->when($minPrice !== null, fn($query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn($query) => $query->where('price', '<=', $maxPrice))
            ->when($regionId !== null, fn($query) => $query->where('region_id', $regionId))
            ->when($cityId !== null, fn($query) => $query->where('city_id', $cityId))
            ->when($isNegotiable !== null, fn($query) => $query->where('is_negotiable', $isNegotiable));

        $this->applyCategoryFilter($query, $categoryId);
        $this->applyLocationSearchFilter($query, 'region', $regionQuery);
        $this->applyLocationSearchFilter($query, 'city', $cityQuery);
        $this->applyPublicSort($query, $sort);

        return $query->paginate(
            perPage: $perPage,
            pageName: 'page',
            page: $page,
        );
    }

    public function findPublicPublishedById(string $listingId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition', 'orderedMedia', 'user'])
            ->whereKey($listingId)
            ->where('status', ListingStatus::PUBLISHED)
            ->first();
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
    private function applyCategoryFilter(Builder $query, ?string $categoryId): void
    {
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
}
