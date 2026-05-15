<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class UpdateCategoryAttributeDefinitionHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
        private CategoryAttributeDefinitionPayloadMapper $payloadMapper,
    ) {}

    public function execute(UpdateCategoryAttributeDefinitionInput $input): UpdateCategoryAttributeDefinitionOutput
    {
        $definition = $this->repository->findById($input->attributeDefinitionId);

        if ($definition === null) {
            throw (new ModelNotFoundException())->setModel(EloquentCategoryAttributeDefinition::class, [$input->attributeDefinitionId]);
        }

        $definition = $this->repository->save([
            'category_id'         => $input->categoryId,
            'name'                => $input->name,
            'slug'                => $input->slug,
            'type'                => $input->type,
            'unit'                => $input->unit,
            'description'         => $input->description,
            'placeholder'         => $input->placeholder,
            'help_text'           => $input->helpText,
            'default_value'       => $input->defaultValue,
            'group_name'          => $input->groupName,
            'options'             => $input->options,
            'is_required'         => $input->isRequired,
            'is_filterable'       => $input->isFilterable,
            'show_in_card'        => $input->showInCard,
            'is_active'           => $input->isActive,
            'applies_to_children' => $input->appliesToChildren,
            'sort_order'          => $input->sortOrder,
        ], $input->attributeDefinitionId);

        return UpdateCategoryAttributeDefinitionOutput::from([
            'item' => $this->payloadMapper->map($definition),
        ]);
    }
}
