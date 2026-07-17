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
     * @return array<string, string>
     */
    public function parentOptions(?string $ignoreId = null): array
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
            ->with('iconMedia')
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
    public function listBranch(string $categoryId, bool $onlyActive = true): Collection
    {
        $rootCategory = $this->findById($categoryId);

        if ($rootCategory === null) {
            return collect();
        }

        $query        = EloquentCategory::query()
            ->with('iconMedia')
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
        ?string $id = null,
    ): EloquentCategory {
        $category       = $id !== null
            ? EloquentCategory::query()->findOrFail($id)
            : new EloquentCategory();
        $normalized     = $this->categoryInputNormalizer->normalize($attributes, $category);
        $parentId       = is_string($normalized['parent_id']) ? $normalized['parent_id'] : null;

        $this->categoryHierarchyService->assertParentIsValid($category, $parentId);

        $category->fill($normalized);
        $category->save();

        $this->categoryHierarchyService->sync($category);

        return $category->fresh(['parentCategory']) ?? $category;
    }

    public function findById(string $id): ?EloquentCategory
    {
        return EloquentCategory::query()
            ->with('iconMedia')
            ->find($id);
    }
}
