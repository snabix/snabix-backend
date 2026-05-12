<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use App\Catalog\Application\UseCases\ListCategories\ListCategoriesHandler;
use App\Catalog\Application\UseCases\ListCategories\ListCategoriesInput;
use OpenApi\Attributes as OA;

class ListCategoriesController
{
    #[OA\Get(
        path: '/api/v1/categories',
        operationId: 'categoriesIndex',
        summary: 'Получить список категорий в виде дерева или плоского списка',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'only_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'tree', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Коллекция категорий',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoriesResponse'),
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function __invoke(
        ListCategoriesRequest $request,
        ListCategoriesHandler $handler,
    ): ListCategoriesResponse {
        $result = $handler->execute(
            ListCategoriesInput::from([
                'onlyActive' => $request->onlyActive(),
                'tree'       => $request->tree(),
            ]),
        );

        return ListCategoriesResponse::make($result);
    }
}
