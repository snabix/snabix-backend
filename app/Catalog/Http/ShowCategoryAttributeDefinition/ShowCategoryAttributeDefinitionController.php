<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition\ShowCategoryAttributeDefinitionHandler;
use App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition\ShowCategoryAttributeDefinitionInput;

class ShowCategoryAttributeDefinitionController
{
    public function __invoke(
        ShowCategoryAttributeDefinitionRequest $request,
        ShowCategoryAttributeDefinitionHandler $handler,
    ): ShowCategoryAttributeDefinitionResponse {
        $result = $handler->execute(ShowCategoryAttributeDefinitionInput::from($request->inputData()));

        return ShowCategoryAttributeDefinitionResponse::make($result);
    }
}
