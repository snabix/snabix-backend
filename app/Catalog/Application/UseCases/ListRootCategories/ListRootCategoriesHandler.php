<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListRootCategories;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;

readonly class ListRootCategoriesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(ListRootCategoriesInput $input): ListRootCategoriesOutput
    {
        $categories = $this->categoryRepository->listRootCategories(
            $input->onlyActive,
        );

        return ListRootCategoriesOutput::from([
            'items' => $categories
                ->map(fn (EloquentCategory $category): array => $this->mapCategory($category))
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCategory(EloquentCategory $category): array
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
            'children'    => [],
        ];
    }
}
