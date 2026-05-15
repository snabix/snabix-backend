<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryBranch;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

readonly class ShowCategoryBranchHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(ShowCategoryBranchInput $input): ShowCategoryBranchOutput
    {
        $rootCategory     = $this->categoryRepository->findById($input->categoryId);

        if ($rootCategory === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategory::class, [$input->categoryId]);
        }

        $branchCategories = $this->categoryRepository->listBranch(
            $input->categoryId,
            $input->onlyActive,
        );

        return ShowCategoryBranchOutput::from([
            'item' => $this->mapCategory(
                $rootCategory,
                $branchCategories,
            ),
        ]);
    }

    /**
     * @param  Collection<int, EloquentCategory> $branchCategories
     * @return array<string, mixed>
     */
    private function mapCategory(
        EloquentCategory $category,
        Collection $branchCategories,
    ): array {
        $children = $branchCategories
            ->where('parent_id', $category->id)
            ->map(fn(EloquentCategory $child): array => $this->mapCategory($child, $branchCategories))
            ->values()
            ->all();

        return [
            'id'               => $category->id,
            'catalogType'      => $category->catalog_type->value,
            'catalogTypeLabel' => $category->catalog_type->label(),
            'parentId'         => $category->parent_id,
            'name'             => $category->name,
            'slug'             => $category->slug,
            'description'      => $category->description,
            'sortOrder'        => $category->sort_order,
            'isActive'         => $category->is_active,
            'path'             => $category->path,
            'depth'            => $category->depth,
            'children'         => $children,
        ];
    }
}
