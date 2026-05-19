<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ListCategoryAttributeDefinitions\ListCategoryAttributeDefinitionsHandler;
use App\Catalog\Application\UseCases\ListCategoryAttributeDefinitions\ListCategoryAttributeDefinitionsInput;

class ListCategoryAttributeDefinitionsController
{
    public function __invoke(
        ListCategoryAttributeDefinitionsRequest $request,
        ListCategoryAttributeDefinitionsHandler $handler,
    ): ListCategoryAttributeDefinitionsResponse {
        $result = $handler->execute(ListCategoryAttributeDefinitionsInput::from($request->inputData()));

        return ListCategoryAttributeDefinitionsResponse::make($result);
    }
}
