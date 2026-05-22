<?php

declare(strict_types=1);

namespace App\Catalog\Http\ImportCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions\ImportCategoryAttributeDefinitionsHandler;
use App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions\ImportCategoryAttributeDefinitionsInput;

class ImportCategoryAttributeDefinitionsController
{
    public function __invoke(
        ImportCategoryAttributeDefinitionsRequest $request,
        ImportCategoryAttributeDefinitionsHandler $handler,
    ): ImportCategoryAttributeDefinitionsResponse {
        $result = $handler->execute(ImportCategoryAttributeDefinitionsInput::from($request->inputData()));

        return ImportCategoryAttributeDefinitionsResponse::make($result);
    }
}
