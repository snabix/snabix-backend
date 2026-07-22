<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\GetCategoryAttributes;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Shared\Application\Support\ReferenceDataCache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class GetCategoryAttributesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private CategoryAttributeDefinitionRepositoryInterface $categoryAttributeDefinitionRepository,
        private ReferenceDataCache $cache,
    ) {}

    public function execute(GetCategoryAttributesInput $input): GetCategoryAttributesOutput
    {
        return GetCategoryAttributesOutput::from($this->cache->rememberCatalog(
            'catalog:attributes:' . $input->categoryId . ':only-active:' . (int) $input->onlyActive,
            fn(): array => $this->payload($input),
        ));
    }

    /**
     * @return array{category: array<string, mixed>, items: list<array<string, mixed>>}
     */
    private function payload(GetCategoryAttributesInput $input): array
    {
        $category = $this->categoryRepository->findById($input->categoryId);

        if ($category === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategory::class, [$input->categoryId]);
        }

        return [
            'category' => [
                'id'               => $category->id,
                'catalogKind'      => $category->catalog_type->apiName(),
                'catalogKindLabel' => $category->catalog_type->label(),
                // Deprecated compatibility aliases. Remove after 2026-10-31.
                'catalogType'      => $category->catalog_type->value,
                'catalogTypeLabel' => $category->catalog_type->label(),
                'parentId'         => $category->parent_id,
                'name'             => $category->name,
                'slug'             => $category->slug,
            ],
            'items'    => array_values($this->categoryAttributeDefinitionRepository
                ->forCategory($input->categoryId, $input->onlyActive)
                ->map(
                    fn(EloquentCategoryAttributeDefinition $definition): array => [
                        'id'                => $definition->id,
                        'categoryId'        => $definition->category_id,
                        'isInherited'       => $definition->category_id !== $category->id,
                        'name'              => $definition->name,
                        'slug'              => $definition->slug,
                        'valueType'         => $definition->type->apiName(),
                        'valueTypeLabel'    => $definition->type->label(),
                        // Deprecated compatibility aliases. Remove after 2026-10-31.
                        'type'              => $definition->type->value,
                        'typeLabel'         => $definition->type->label(),
                        'unit'              => $definition->unit,
                        'description'       => $definition->description,
                        'placeholder'       => $definition->placeholder,
                        'helpText'          => $definition->help_text,
                        'defaultValue'      => $definition->default_value,
                        'groupName'         => $definition->group_name,
                        'options'           => $definition->options,
                        'dependencyRules'   => $definition->dependency_rules,
                        'schemaVersion'     => $definition->schema_version,
                        'isRequired'        => $definition->is_required,
                        'isFilterable'      => $definition->is_filterable,
                        'showInCard'        => $definition->show_in_card,
                        'isActive'          => $definition->is_active,
                        'appliesToChildren' => $definition->applies_to_children,
                        'sortOrder'         => $definition->sort_order,
                    ],
                )
                ->all()),
        ];
    }
}
