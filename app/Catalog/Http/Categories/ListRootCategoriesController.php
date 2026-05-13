<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesHandler;
use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesInput;
use OpenApi\Attributes as OA;

class ListRootCategoriesController
{
    #[OA\Get(
        path: '/api/v1/categories/list',
        operationId: 'categoriesRootIndex',
        summary: 'Получить корневые категории',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'only_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Коллекция корневых категорий',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoriesResponse'),
            ),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function __invoke(
        ListRootCategoriesRequest $request,
        ListRootCategoriesHandler $handler,
    ): ListCategoriesResponse {
        $result = $handler->execute(
            ListRootCategoriesInput::from([
                'onlyActive' => $request->onlyActive(),
            ]),
        );

        return ListCategoriesResponse::make($result);
    }
}
