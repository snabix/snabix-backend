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
        $result = $handler->execute(CreateCategoryAttributeDefinitionInput::from($request->inputData()));

        return CreateCategoryAttributeDefinitionResponse::make($result);
    }
}
