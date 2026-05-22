<?php

declare(strict_types=1);

namespace App\Catalog\Http\ExportCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions\ExportCategoryAttributeDefinitionsHandler;
use App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions\ExportCategoryAttributeDefinitionsInput;

class ExportCategoryAttributeDefinitionsController
{
    public function __invoke(
        ExportCategoryAttributeDefinitionsRequest $request,
        ExportCategoryAttributeDefinitionsHandler $handler,
    ): ExportCategoryAttributeDefinitionsResponse {
        $result = $handler->execute(ExportCategoryAttributeDefinitionsInput::from($request->inputData()));

        return ExportCategoryAttributeDefinitionsResponse::make($result);
    }
}
