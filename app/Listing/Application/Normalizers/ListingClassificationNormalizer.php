<?php

declare(strict_types=1);

namespace App\Listing\Application\Normalizers;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Application\Support\NormalizedListingClassification;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class ListingClassificationNormalizer
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function normalize(
        string $categoryId,
        int $type,
        ?int $condition,
    ): NormalizedListingClassification {
        $category          = $this->resolveCategory($categoryId);
        $resolvedType      = $this->resolveType($type);
        $resolvedCondition = $this->resolveCondition($condition, $resolvedType);

        $this->assertTypeMatchesCategory($resolvedType, $category);

        return new NormalizedListingClassification(
            category: $category,
            type: $resolvedType,
            condition: $resolvedCondition,
        );
    }

    private function resolveCategory(string $categoryId): EloquentCategory
    {
        if (! Str::isUuid($categoryId)) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория объявления обязательна.'],
            ]);
        }

        $category = $this->categoryRepository->findById($categoryId);

        if ($category === null) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория не найдена.'],
            ]);
        }

        return $category;
    }

    private function resolveType(int $type): ListingType
    {
        $resolved = ListingType::tryFrom($type);

        if ($resolved === null) {
            throw ValidationException::withMessages([
                'type' => ['Укажите корректный тип объявления.'],
            ]);
        }

        return $resolved;
    }

    private function resolveCondition(?int $condition, ListingType $type): ListingCondition
    {
        if ($type === ListingType::SERVICE) {
            return ListingCondition::NOT_APPLICABLE;
        }

        return $condition === null
            ? ListingCondition::USED
            : ListingCondition::tryFrom($condition) ?? ListingCondition::USED;
    }

    private function assertTypeMatchesCategory(
        ListingType $type,
        EloquentCategory $category,
    ): void {
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
