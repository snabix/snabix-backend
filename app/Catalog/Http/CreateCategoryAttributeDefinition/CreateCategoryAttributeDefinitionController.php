<?php

declare(strict_types=1);

namespace App\Catalog\Http\CreateCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\CreateCategoryAttributeDefinition\CreateCategoryAttributeDefinitionHandler;
use App\Catalog\Application\UseCases\CreateCategoryAttributeDefinition\CreateCategoryAttributeDefinitionInput;

class CreateCategoryAttributeDefinitionController
{
    public function __invoke(
        CreateCategoryAttributeDefinitionRequest $request,
        CreateCategoryAttributeDefinitionHandler $handler,
    ): CreateCategoryAttributeDefinitionResponse {
        $request->validated();
        $options   = $request->input('options');

        $result    = $handler->execute(
            CreateCategoryAttributeDefinitionInput::from([
                'categoryId'         => $request->integer('categoryId'),
                'name'               => $request->string('name')->toString(),
                'slug'               => $request->filled('slug') ? $request->string('slug')->toString() : null,
                'type'               => $request->integer('type'),
                'unit'               => $request->filled('unit') ? $request->string('unit')->toString() : null,
                'description'        => $request->filled('description') ? $request->string('description')->toString() : null,
                'placeholder'        => $request->filled('placeholder') ? $request->string('placeholder')->toString() : null,
                'helpText'           => $request->filled('helpText') ? $request->string('helpText')->toString() : null,
                'defaultValue'       => is_array($request->input('defaultValue')) ? $request->input('defaultValue') : null,
                'groupName'          => $request->filled('groupName') ? $request->string('groupName')->toString() : null,
                'options'            => is_array($options) ? array_values($options) : null,
                'isRequired'         => $request->boolean('isRequired', false),
                'isFilterable'       => $request->boolean('isFilterable', false),
                'showInCard'         => $request->boolean('showInCard', false),
                'isActive'           => $request->boolean('isActive', true),
                'appliesToChildren'  => $request->boolean('appliesToChildren', true),
                'sortOrder'          => $request->integer('sortOrder'),
            ]),
        );

        return CreateCategoryAttributeDefinitionResponse::make($result);
    }
}
