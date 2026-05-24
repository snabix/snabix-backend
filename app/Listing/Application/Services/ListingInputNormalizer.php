<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Application\Support\NormalizedListingData;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

readonly class ListingInputNormalizer
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     */
    public function normalizeForCreate(array $attributes): NormalizedListingData
    {
        $category  = $this->resolveCategory($attributes['category_id'] ?? null);
        $type      = $this->resolveType($attributes['type'] ?? null);
        $condition = $this->resolveCondition($attributes['condition'] ?? null, $type);

        $this->assertTypeMatchesCategory($type, $category);

        return new NormalizedListingData(
            category: $category,
            attributes: [
                'user_id'          => $this->resolveUserId($attributes['user_id'] ?? null),
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $this->resolveStatus($attributes['status'] ?? null),
                'condition'        => $condition,
                'title'            => $this->resolveTitle($attributes['title'] ?? null),
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
            ],
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function normalizeForUpdate(
        EloquentListing $listing,
        array           $attributes,
    ): NormalizedListingData {
        $category  = $this->resolveCategory($attributes['category_id'] ?? $listing->category_id);
        $type      = $this->resolveType($attributes['type'] ?? $listing->type);
        $condition = $this->resolveCondition($attributes['condition'] ?? $listing->condition, $type);

        $this->assertTypeMatchesCategory($type, $category);

        return new NormalizedListingData(
            category: $category,
            attributes: [
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $this->resolveStatus($attributes['status'] ?? $listing->status),
                'condition'        => $condition,
                'title'            => $this->resolveTitle($attributes['title'] ?? $listing->title),
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
            ],
        );
    }

    private function resolveCategory(mixed $categoryId): EloquentCategory
    {
        if (! is_numeric($categoryId)) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория объявления обязательна.'],
            ]);
        }

        $category = $this->categoryRepository->findById((int) $categoryId);

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
        int   $limit = 255,
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
}
