<?php

declare(strict_types=1);

namespace App\Catalog\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;

class CategoryAttributeDefinitionPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(EloquentCategoryAttributeDefinition $definition): array
    {
        return [
            'id'                => $definition->id,
            'category'          => $definition->category === null
                ? null
                : [
                    'id'               => $definition->category->id,
                    'catalogType'      => $definition->category->catalog_type->value,
                    'catalogTypeLabel' => $definition->category->catalog_type->label(),
                    'parentId'         => $definition->category->parent_id,
                    'name'             => $definition->category->name,
                    'slug'             => $definition->category->slug,
                    'fullName'         => $definition->category->full_name,
                ],
            'categoryId'        => $definition->category_id,
            'name'              => $definition->name,
            'slug'              => $definition->slug,
            'type'              => $definition->type->value,
            'typeLabel'         => $definition->type->label(),
            'unit'              => $definition->unit,
            'description'       => $definition->description,
            'placeholder'       => $definition->placeholder,
            'helpText'          => $definition->help_text,
            'defaultValue'      => $definition->default_value,
            'groupName'         => $definition->group_name,
            'options'           => $definition->options,
            'isRequired'        => $definition->is_required,
            'isFilterable'      => $definition->is_filterable,
            'showInCard'        => $definition->show_in_card,
            'isActive'          => $definition->is_active,
            'appliesToChildren' => $definition->applies_to_children,
            'sortOrder'         => $definition->sort_order,
        ];
    }
}
