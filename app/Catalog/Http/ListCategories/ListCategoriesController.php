<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategories;

use App\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Catalog\Application\UseCases\ListCategories\ListCategoriesInput;

class ListCategoriesController
{
    public function __invoke(
        ListCategoriesRequest $request,
        ListCategoriesHandler $handler,
    ): ListCategoriesResponse {
        $validated = $request->validated();

        $result    = $handler->execute(
            ListCategoriesInput::from([
                'onlyActive' => (bool) ($validated['only_active'] ?? true),
                'tree'       => (bool) ($validated['tree'] ?? true),
            ]),
        );

        return ListCategoriesResponse::make($result);
    }
}
