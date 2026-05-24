<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryInputNormalizer
{
    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function normalize(array $attributes, EloquentCategory $category): array
    {
        $name           = $this->resolveName($attributes['name'] ?? null);
        $rawDescription = $attributes['description'] ?? null;

        return [
            'parent_id'    => $this->resolveParentId($attributes['parent_id'] ?? null),
            'catalog_type' => $this->resolveCatalogType($attributes['catalog_type'] ?? null),
            'name'         => $name,
            'slug'         => $this->generateUniqueSlug(
                name: $name,
                slug: $attributes['slug'] ?? null,
                ignoreId: $category->exists ? (int) $category->id : null,
            ),
            'description'  => is_string($rawDescription) ? $rawDescription : null,
            'sort_order'   => $this->resolveSortOrder($attributes['sort_order'] ?? 0),
            'is_active'    => (bool) ($attributes['is_active'] ?? true),
        ];
    }

    private function resolveName(mixed $name): string
    {
        $resolvedName = is_string($name) ? trim($name) : '';

        if ($resolvedName === '') {
            throw ValidationException::withMessages([
                'name' => 'Название категории обязательно.',
            ]);
        }

        return $resolvedName;
    }

    private function resolveParentId(mixed $parentId): ?int
    {
        return is_numeric($parentId) ? (int) $parentId : null;
    }

    private function resolveSortOrder(mixed $sortOrder): int
    {
        return is_numeric($sortOrder) ? (int) $sortOrder : 0;
    }

    private function resolveCatalogType(mixed $catalogType): CategoryCatalogType
    {
        if ($catalogType instanceof CategoryCatalogType) {
            return $catalogType;
        }

        if (is_int($catalogType)) {
            return CategoryCatalogType::tryFrom($catalogType) ?? CategoryCatalogType::PRODUCT;
        }

        if (is_string($catalogType) && is_numeric($catalogType)) {
            return CategoryCatalogType::tryFrom((int) $catalogType) ?? CategoryCatalogType::PRODUCT;
        }

        return CategoryCatalogType::PRODUCT;
    }

    private function generateUniqueSlug(
        string $name,
        mixed $slug,
        ?int $ignoreId = null,
    ): string {
        $baseSource = is_string($slug) && trim($slug) !== '' ? trim($slug) : $name;
        $baseSlug   = Str::slug($baseSource);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'Не удалось сформировать slug автоматически. Укажите slug вручную.',
            ]);
        }

        $candidate  = $baseSlug;
        $counter    = 2;

        while (
            EloquentCategory::query()
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
