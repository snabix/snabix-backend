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
        $result = $handler->execute(ListCategoriesInput::from($request->inputData()));

        return ListCategoriesResponse::make($result);
    }
}
