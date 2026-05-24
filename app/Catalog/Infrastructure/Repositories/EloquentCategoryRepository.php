<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repositories;

use App\Catalog\Application\Services\CategoryHierarchyService;
use App\Catalog\Application\Services\CategoryInputNormalizer;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Support\Collection;

readonly class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private CategoryInputNormalizer  $categoryInputNormalizer,
        private CategoryHierarchyService $categoryHierarchyService,
    ) {}

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
            ->filter(fn(EloquentCategory $category): bool => $this->categoryHierarchyService->isAllowedParentOption(
                category: $category,
                ignoreId: $ignoreId,
                ignoredPath: $ignoredPath,
            ))
            ->mapWithKeys(fn(EloquentCategory $category): array => [$category->id => $this->categoryHierarchyService->indentedName($category)])
            ->all();
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
        $normalized     = $this->categoryInputNormalizer->normalize($attributes, $category);
        $parentId       = is_int($normalized['parent_id']) ? $normalized['parent_id'] : null;

        $this->categoryHierarchyService->assertParentIsValid($category, $parentId);

        $category->fill($normalized);
        $category->save();

        $this->categoryHierarchyService->sync($category);

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
}
