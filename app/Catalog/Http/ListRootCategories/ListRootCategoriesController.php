<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListRootCategories;

use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesHandler;
use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesInput;

class ListRootCategoriesController
{
    public function __invoke(
        ListRootCategoriesRequest $request,
        ListRootCategoriesHandler $handler,
    ): ListRootCategoriesResponse {
        $result = $handler->execute(ListRootCategoriesInput::from($request->inputData()));

        return ListRootCategoriesResponse::make($result);
    }
}
