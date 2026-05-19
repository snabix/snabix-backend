<?php

declare(strict_types=1);

namespace App\Catalog\Http\UpdateCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition\UpdateCategoryAttributeDefinitionHandler;
use App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition\UpdateCategoryAttributeDefinitionInput;

class UpdateCategoryAttributeDefinitionController
{
    public function __invoke(
        UpdateCategoryAttributeDefinitionRequest $request,
        UpdateCategoryAttributeDefinitionHandler $handler,
    ): UpdateCategoryAttributeDefinitionResponse {
        $result = $handler->execute(UpdateCategoryAttributeDefinitionInput::from($request->inputData()));

        return UpdateCategoryAttributeDefinitionResponse::make($result);
    }
}
