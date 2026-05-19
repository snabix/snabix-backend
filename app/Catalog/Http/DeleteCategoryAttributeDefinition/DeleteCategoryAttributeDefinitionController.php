<?php

declare(strict_types=1);

namespace App\Catalog\Http\DeleteCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition\DeleteCategoryAttributeDefinitionHandler;
use App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition\DeleteCategoryAttributeDefinitionInput;

class DeleteCategoryAttributeDefinitionController
{
    public function __invoke(
        DeleteCategoryAttributeDefinitionRequest $request,
        DeleteCategoryAttributeDefinitionHandler $handler,
    ): DeleteCategoryAttributeDefinitionResponse {
        $result = $handler->execute(
            DeleteCategoryAttributeDefinitionInput::from([
                'attributeDefinitionId' => $request->attributeDefinitionId(),
            ]),
        );

        return DeleteCategoryAttributeDefinitionResponse::make($result);
    }
}
