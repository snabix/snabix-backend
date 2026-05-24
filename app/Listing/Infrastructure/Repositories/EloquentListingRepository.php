<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
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
            $category   = $this->resolveCategory($attributes['category_id'] ?? null);
            $type       = $this->resolveType($attributes['type'] ?? null);
            $condition  = $this->resolveCondition($attributes['condition'] ?? null, $type);
            $status     = $this->resolveStatus($attributes['status'] ?? null);
            $title      = $this->resolveTitle($attributes['title'] ?? null);
            $slug       = $this->generateUniqueSlug($title);
            $userId     = $this->resolveUserId($attributes['user_id'] ?? null);

            $this->assertTypeMatchesCategory($type, $category);

            $listing    = EloquentListing::query()->create([
                'user_id'          => $userId,
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $status,
                'condition'        => $condition,
                'title'            => $title,
                'slug'             => $slug,
                'description'      => $this->resolveDescription($attributes['description'] ?? null),
                'price'            => $this->resolvePrice($attributes['price'] ?? null),
                'currency'         => $this->resolveCurrency($attributes['currency'] ?? null),
                'is_negotiable'    => (bool) ($attributes['is_negotiable'] ?? false),
                'contact_name'     => $this->resolveNullableString($attributes['contact_name'] ?? null, 120),
                'contact_phone'    => $this->resolveNullableString($attributes['contact_phone'] ?? null, 32),
                'contact_email'    => $this->resolveEmail($attributes['contact_email'] ?? null),
                'views_count'      => $this->resolveViewsCount($attributes['views_count'] ?? 0),
                'is_featured'      => (bool) ($attributes['is_featured'] ?? false),
                'rejection_reason' => $this->resolveNullableString($attributes['rejection_reason'] ?? null, 5000),
                'published_at'     => null,
                'expires_at'       => null,
            ]);

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $category->id,
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
            $category  = $this->resolveCategory($attributes['category_id'] ?? $listing->category_id);
            $type      = $this->resolveType($attributes['type'] ?? $listing->type);
            $condition = $this->resolveCondition($attributes['condition'] ?? $listing->condition, $type);
            $status    = $this->resolveStatus($attributes['status'] ?? $listing->status);
            $title     = $this->resolveTitle($attributes['title'] ?? $listing->title);

            $this->assertTypeMatchesCategory($type, $category);
            $this->listingStatusTransitionPolicy->assertCanTransition($listing->status, $status);

            $listing->fill([
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $status,
                'condition'        => $condition,
                'title'            => $title,
                'slug'             => $this->generateUniqueSlug($title, $listing->id),
                'description'      => $this->resolveDescription($attributes['description'] ?? $listing->description),
                'price'            => $this->resolvePrice($attributes['price'] ?? $listing->price),
                'currency'         => $this->resolveCurrency($attributes['currency'] ?? $listing->currency),
                'is_negotiable'    => (bool) ($attributes['is_negotiable'] ?? $listing->is_negotiable),
                'contact_name'     => $this->resolveNullableString($attributes['contact_name'] ?? $listing->contact_name, 120),
                'contact_phone'    => $this->resolveNullableString($attributes['contact_phone'] ?? $listing->contact_phone, 32),
                'contact_email'    => $this->resolveEmail($attributes['contact_email'] ?? $listing->contact_email),
                'views_count'      => $this->resolveViewsCount($attributes['views_count'] ?? $listing->views_count),
                'is_featured'      => (bool) ($attributes['is_featured'] ?? $listing->is_featured),
                'rejection_reason' => $this->resolveNullableString($attributes['rejection_reason'] ?? $listing->rejection_reason, 5000),
            ]);
            $listing->save();

            $this->listingAttributeValueSynchronizer->sync(
                listing: $listing,
                categoryId: $category->id,
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

    private function resolveCategory(mixed $categoryId): EloquentCategory
    {
        if (! is_numeric($categoryId)) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория объявления обязательна.'],
            ]);
        }

        $category = EloquentCategory::query()->find((int) $categoryId);

        if ($category === null) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория не найдена.'],
            ]);
        }

        return $category;
    }

    private function resolveType(mixed $type): ListingType
    {
        if ($type instanceof ListingType) {
            return $type;
        }

        if (is_int($type)) {
            $resolvedType = ListingType::tryFrom($type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        if (is_string($type) && is_numeric($type)) {
            $resolvedType = ListingType::tryFrom((int) $type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        throw ValidationException::withMessages([
            'type' => ['Укажите корректный тип объявления.'],
        ]);
    }

    private function resolveCondition(mixed $condition, ListingType $type): ListingCondition
    {
        if ($type === ListingType::SERVICE) {
            return ListingCondition::NOT_APPLICABLE;
        }

        if ($condition instanceof ListingCondition) {
            return $condition;
        }

        if (is_int($condition)) {
            $resolvedCondition = ListingCondition::tryFrom($condition);

            if ($resolvedCondition !== null) {
                return $resolvedCondition;
            }
        }

        if (is_string($condition) && is_numeric($condition)) {
            $resolvedCondition = ListingCondition::tryFrom((int) $condition);

            if ($resolvedCondition !== null) {
                return $resolvedCondition;
            }
        }

        return ListingCondition::USED;
    }

    private function resolveStatus(mixed $status): ListingStatus
    {
        if ($status instanceof ListingStatus) {
            return $status;
        }

        if (is_int($status)) {
            return ListingStatus::tryFrom($status) ?? ListingStatus::DRAFT;
        }

        if (is_string($status) && is_numeric($status)) {
            return ListingStatus::tryFrom((int) $status) ?? ListingStatus::DRAFT;
        }

        return ListingStatus::DRAFT;
    }

    private function resolveTitle(mixed $title): string
    {
        $resolvedTitle = is_string($title) ? trim($title) : '';

        if ($resolvedTitle === '') {
            throw ValidationException::withMessages([
                'title' => ['Заголовок объявления обязателен.'],
            ]);
        }

        return $resolvedTitle;
    }

    private function resolveDescription(mixed $description): string
    {
        $resolvedDescription = is_string($description) ? trim($description) : '';

        if ($resolvedDescription === '') {
            throw ValidationException::withMessages([
                'description' => ['Описание объявления обязательно.'],
            ]);
        }

        return $resolvedDescription;
    }

    private function resolvePrice(mixed $price): ?int
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (is_int($price)) {
            return $price;
        }

        if (is_string($price) && preg_match('/^\d+$/', $price) === 1) {
            return (int) $price;
        }

        if (is_float($price) && floor($price) === $price) {
            return (int) $price;
        }

        if (is_string($price) && preg_match('/^\d+\.0+$/', $price) === 1) {
            return (int) $price;
        }

        if (! is_numeric($price)) {
            throw ValidationException::withMessages([
                'price' => ['Цена должна быть целым числом.'],
            ]);
        }

        throw ValidationException::withMessages([
            'price' => ['Цена должна быть целым числом.'],
        ]);
    }

    private function resolveCurrency(mixed $currency): string
    {
        $resolvedCurrency = is_string($currency) ? strtoupper(trim($currency)) : 'RUB';

        if ($resolvedCurrency === '') {
            return 'RUB';
        }

        return Str::limit($resolvedCurrency, 3, '');
    }

    private function resolveUserId(mixed $userId): string
    {
        if (! is_string($userId) || trim($userId) === '') {
            throw ValidationException::withMessages([
                'userId' => ['Пользователь объявления не определён.'],
            ]);
        }

        return $userId;
    }

    private function resolveNullableString(
        mixed $value,
        int $limit = 255,
    ): ?string {
        if (! is_string($value)) {
            return null;
        }

        $resolvedValue = trim($value);

        if ($resolvedValue === '') {
            return null;
        }

        return Str::limit($resolvedValue, $limit, '');
    }

    private function resolveEmail(mixed $email): ?string
    {
        $resolvedEmail = $this->resolveNullableString($email);

        if ($resolvedEmail === null) {
            return null;
        }

        return mb_strtolower($resolvedEmail);
    }

    private function resolveViewsCount(mixed $viewsCount): int
    {
        return is_numeric($viewsCount)
            ? max((int) $viewsCount, 0)
            : 0;
    }

    private function assertTypeMatchesCategory(ListingType $type, EloquentCategory $category): void
    {
        $categoryType = $category->catalog_type;

        if (
            ($type === ListingType::PRODUCT && $categoryType !== CategoryCatalogType::PRODUCT)
            || ($type === ListingType::SERVICE && $categoryType !== CategoryCatalogType::SERVICE)
        ) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория не соответствует выбранному типу объявления.'],
            ]);
        }
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
