<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\GetCategoryAttributes;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCategoryAttributesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private CategoryAttributeDefinitionRepositoryInterface $categoryAttributeDefinitionRepository,
    ) {}

    public function execute(GetCategoryAttributesInput $input): GetCategoryAttributesOutput
    {
        $category    = $this->categoryRepository->findById($input->categoryId);

        if ($category === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategory::class, [$input->categoryId]);
        }

        $definitions = $this->categoryAttributeDefinitionRepository->forCategory(
            $input->categoryId,
            $input->onlyActive,
        );

        return GetCategoryAttributesOutput::from([
            'category' => [
                'id'               => $category->id,
                'catalogType'      => $category->catalog_type->value,
                'catalogTypeLabel' => $category->catalog_type->label(),
                'parentId'         => $category->parent_id,
                'name'             => $category->name,
                'slug'             => $category->slug,
            ],
            'items'    => $definitions
                ->map(
                    fn(EloquentCategoryAttributeDefinition $definition): array => [
                        'id'                => $definition->id,
                        'categoryId'        => $definition->category_id,
                        'isInherited'       => $definition->category_id !== $category->id,
                        'name'              => $definition->name,
                        'slug'              => $definition->slug,
                        'type'              => $definition->type->value,
                        'typeLabel'         => $definition->type->label(),
                        'unit'              => $definition->unit,
                        'description'       => $definition->description,
                        'options'           => $definition->options,
                        'isRequired'        => $definition->is_required,
                        'isFilterable'      => $definition->is_filterable,
                        'isActive'          => $definition->is_active,
                        'appliesToChildren' => $definition->applies_to_children,
                        'sortOrder'         => $definition->sort_order,
                    ],
                )
                ->values()
                ->all(),
        ]);
    }
}
