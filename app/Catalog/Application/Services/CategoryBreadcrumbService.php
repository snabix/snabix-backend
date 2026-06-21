<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategory;

final class CategoryBreadcrumbService
{
    /**
     * @var array<string, array{
     *     breadcrumbs: list<array{id: string, name: string, slug: string}>,
     *     fullName: string
     * }>
     */
    private array $resolvedTrails = [];

    /** @var array<string, array{id: string, parentId: string|null, name: string, slug: string}>|null */
    private ?array $categoryIndex = null;

    /**
     * @return array{
     *     breadcrumbs: list<array{id: string, name: string, slug: string}>,
     *     fullName: string
     * }
     */
    public function resolve(EloquentCategory $category): array
    {
        if (isset($this->resolvedTrails[$category->id])) {
            return $this->resolvedTrails[$category->id];
        }

        $categoryIndex                              = $this->categoryIndex();
        $categoryIndex[$category->id]               = $this->categoryData($category);
        $breadcrumbs                                = [];
        $visitedIds                                 = [];
        $currentId                                  = $category->id;

        while (isset($categoryIndex[$currentId]) && ! isset($visitedIds[$currentId])) {
            $current                = $categoryIndex[$currentId];
            $visitedIds[$currentId] = true;
            $breadcrumbs[]          = [
                'id'   => $current['id'],
                'name' => $current['name'],
                'slug' => $current['slug'],
            ];

            if ($current['parentId'] === null) {
                break;
            }

            $currentId              = $current['parentId'];
        }

        $breadcrumbs                                = array_reverse($breadcrumbs);

        return $this->resolvedTrails[$category->id] = [
            'breadcrumbs' => $breadcrumbs,
            'fullName'    => implode(' / ', array_column($breadcrumbs, 'name')),
        ];
    }

    /**
     * @return array<string, array{id: string, parentId: string|null, name: string, slug: string}>
     */
    private function categoryIndex(): array
    {
        if ($this->categoryIndex !== null) {
            return $this->categoryIndex;
        }

        $this->categoryIndex = [];

        foreach (EloquentCategory::query()->get(['id', 'parent_id', 'name', 'slug']) as $category) {
            $this->categoryIndex[$category->id] = $this->categoryData($category);
        }

        return $this->categoryIndex;
    }

    /**
     * @return array{id: string, parentId: string|null, name: string, slug: string}
     */
    private function categoryData(EloquentCategory $category): array
    {
        return [
            'id'       => $category->id,
            'parentId' => $category->parent_id,
            'name'     => $category->name,
            'slug'     => $category->slug,
        ];
    }
}
