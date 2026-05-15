<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repositories;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function count(): int
    {
        return EloquentCategory::query()->count();
    }

    /**
     * @return array<int, string>
     */
    public function parentOptions(?int $ignoreId = null): array
    {
        $query       = EloquentCategory::query()
            ->orderBy('path')
            ->orderBy('sort_order')
            ->orderBy('name');

        $ignoredPath = null;

        if ($ignoreId !== null) {
            $current = EloquentCategory::query()->find($ignoreId);

            if ($current !== null) {
                $ignoredPath = $current->path;
            }
        }

        return $query
            ->get()
            ->filter(function (EloquentCategory $category) use ($ignoreId, $ignoredPath): bool {
                if ($ignoreId !== null && $category->id === $ignoreId) {
                    return false;
                }

                if ($ignoredPath === null || $category->path === null) {
                    return true;
                }

                return $category->path !== $ignoredPath
                    && !Str::startsWith($category->path, $ignoredPath . '/');
            })
            ->mapWithKeys(fn(EloquentCategory $category): array => [$category->id => $this->indentedName($category)])
            ->all();
    }

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listOrdered(bool $onlyActive = true): Collection
    {
        $query = EloquentCategory::query()
            ->with('parentCategory')
            ->withCount('children')
            ->orderBy('path')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listRootCategories(bool $onlyActive = true): Collection
    {
        $query = EloquentCategory::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, EloquentCategory>
     */
    public function listBranch(int $categoryId, bool $onlyActive = true): Collection
    {
        $rootCategory = $this->findById($categoryId);

        if ($rootCategory === null) {
            return collect();
        }

        $query        = EloquentCategory::query()
            ->where('path', 'like', $rootCategory->path . '/%')
            ->whereBetween('depth', [
                $rootCategory->depth + 1,
                $rootCategory->depth + 2,
            ])
            ->orderBy('depth')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(
        array $attributes,
        ?int  $id = null,
    ): EloquentCategory {
        $category       = $id !== null
            ? EloquentCategory::query()->findOrFail($id)
            : new EloquentCategory();

        $rawName        = $attributes['name'] ?? null;
        $name           = is_string($rawName) ? trim($rawName) : '';

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => 'Название категории обязательно.',
            ]);
        }

        $parentId       = $attributes['parent_id'] ?? null;
        $parentId       = is_numeric($parentId) ? (int) $parentId : null;

        $this->assertParentIsValid($category, $parentId);

        $rawSlug        = $attributes['slug'] ?? null;
        $rawDescription = $attributes['description'] ?? null;
        $rawSortOrder   = $attributes['sort_order'] ?? 0;

        $slug           = $this->generateUniqueSlug(
            name: $name,
            slug: is_string($rawSlug) ? $rawSlug : null,
            ignoreId: $category->exists ? (int) $category->id : null,
        );

        $category->fill([
            'parent_id'    => $parentId,
            'catalog_type' => $this->resolveCatalogType($attributes['catalog_type'] ?? null),
            'name'         => $name,
            'slug'         => $slug,
            'description'  => is_string($rawDescription) ? $rawDescription : null,
            'sort_order'   => is_numeric($rawSortOrder) ? (int) $rawSortOrder : 0,
            'is_active'    => (bool) ($attributes['is_active'] ?? true),
        ]);
        $category->save();

        $this->syncHierarchy($category);

        return $category->fresh(['parentCategory']) ?? $category;
    }

    public function findByParentAndName(
        ?int   $parentId,
        string $name,
    ): ?EloquentCategory {
        return EloquentCategory::query()
            ->where('parent_id', $parentId)
            ->where('name', $name)
            ->first();
    }

    public function findById(int $id): ?EloquentCategory
    {
        return EloquentCategory::query()->find($id);
    }

    private function assertParentIsValid(
        EloquentCategory $category,
        ?int             $parentId,
    ): void {
        if ($parentId === null) {
            return;
        }

        if ($category->exists && $category->id === $parentId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Категория не может быть родителем самой себе.',
            ]);
        }

        $visited         = [];
        $currentParentId = $parentId;

        while ($currentParentId !== null) {
            if ($category->exists && $currentParentId === $category->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Нельзя выбрать дочернюю категорию родителем текущей категории.',
                ]);
            }

            if (in_array($currentParentId, $visited, true)) {
                break;
            }

            $visited[]       = $currentParentId;
            $parent          = EloquentCategory::query()->find($currentParentId);
            $currentParentId = $parent?->parent_id;
        }
    }

    private function generateUniqueSlug(
        string  $name,
        ?string $slug,
        ?int    $ignoreId = null,
    ): string {
        $baseSource = trim($slug ?? '') !== '' ? (string) $slug : $name;
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

    private function syncHierarchy(EloquentCategory $category): void
    {
        $category->refresh();

        $parent = $category->parentCategory()->first();
        $depth  = $parent !== null ? $parent->depth + 1 : 0;
        $path   = $parent !== null ? $parent->path . '/' . $category->slug : $category->slug;

        if ($category->depth !== $depth || $category->path !== $path) {
            $category->forceFill([
                'depth' => $depth,
                'path'  => $path,
            ])
                ->saveQuietly();
        }

        EloquentCategory::query()
            ->where('parent_id', $category->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->each(fn(EloquentCategory $child): EloquentCategory => $this->save($child->toArray(), (int) $child->id));
    }

    private function indentedName(EloquentCategory $category): string
    {
        return str_repeat('— ', max($category->depth, 0)) . $category->name;
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
}
