<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListCategories;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;

readonly class ListCategoriesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(ListCategoriesInput $data): ListCategoriesOutput
    {
        $categories = $this->categoryRepository->listOrdered(
            $data->onlyActive,
        );

        if ($data->tree) {
            return ListCategoriesOutput::from([
                'items' => $this->buildTree($categories, null),
            ]);
        }

        return ListCategoriesOutput::from([
            'items' => $categories
                ->map(fn(EloquentCategory $category): array => $this->mapCategory($category, []))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, EloquentCategory> $categories
     * @return array<int, array<string, mixed>>
     */
    private function buildTree($categories, ?int $parentId): array
    {
        return $categories
            ->where('parent_id', $parentId)
            ->map(fn(EloquentCategory $category): array => $this->mapCategory(
                $category,
                $this->buildTree($categories, (int) $category->id),
            ))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>> $children
     * @return array<string, mixed>
     */
    private function mapCategory(EloquentCategory $category, array $children): array
    {
        return [
            'id'          => $category->id,
            'parentId'    => $category->parent_id,
            'name'        => $category->name,
            'slug'        => $category->slug,
            'description' => $category->description,
            'sortOrder'   => $category->sort_order,
            'isActive'    => $category->is_active,
            'path'        => $category->path,
            'depth'       => $category->depth,
            'children'    => $children,
        ];
    }
}
