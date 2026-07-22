<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryBranch;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Shared\Application\Support\ReferenceDataCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

readonly class ShowCategoryBranchHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ReferenceDataCache $cache,
    ) {}

    public function execute(ShowCategoryBranchInput $input): ShowCategoryBranchOutput
    {
        return ShowCategoryBranchOutput::from([
            'item' => $this->cache->rememberCatalog(
                'catalog:branch:' . $input->categoryId . ':only-active:' . (int) $input->onlyActive,
                function () use ($input): array {
                    $rootCategory = $this->categoryRepository->findById($input->categoryId);

                    if ($rootCategory === null) {
                        throw (new ModelNotFoundException())->setModel(EloquentCategory::class, [$input->categoryId]);
                    }

                    return $this->mapCategory(
                        $rootCategory,
                        $this->categoryRepository->listBranch(
                            $input->categoryId,
                            $input->onlyActive,
                        ),
                    );
                },
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
            'catalogKind'      => $category->catalog_type->apiName(),
            'catalogKindLabel' => $category->catalog_type->label(),
            // Deprecated compatibility aliases. Remove after 2026-10-31.
            'catalogType'      => $category->catalog_type->value,
            'catalogTypeLabel' => $category->catalog_type->label(),
            'parentId'         => $category->parent_id,
            'name'             => $category->name,
            'slug'             => $category->slug,
            'description'      => $category->description,
            'icon'             => $category->iconMedia?->getFullUrl(),
            'sortOrder'        => $category->sort_order,
            'isActive'         => $category->is_active,
            'path'             => $category->path,
            'depth'            => $category->depth,
            'children'         => $children,
        ];
    }
}
