<?php

declare(strict_types=1);

namespace App\Catalog\Http\GetCategoryAttributes;

use App\Catalog\Application\UseCases\GetCategoryAttributes\GetCategoryAttributesHandler;
use App\Catalog\Application\UseCases\GetCategoryAttributes\GetCategoryAttributesInput;

class GetCategoryAttributesController
{
    public function __invoke(
        GetCategoryAttributesRequest $request,
        GetCategoryAttributesHandler $handler,
    ): GetCategoryAttributesResponse {
        $result = $handler->execute(GetCategoryAttributesInput::from($request->inputData()));

        return GetCategoryAttributesResponse::make($result);
    }
}
