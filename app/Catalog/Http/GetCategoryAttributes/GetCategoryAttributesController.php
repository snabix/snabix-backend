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
        $validated = $request->validated();

        $result    = $handler->execute(
            GetCategoryAttributesInput::from([
                'categoryId' => (int) $request->route('categoryId'),
                'onlyActive' => (bool) ($validated['only_active'] ?? true),
            ]),
        );

        return GetCategoryAttributesResponse::make($result);
    }
}
