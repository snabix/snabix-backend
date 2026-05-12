<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchHandler;
use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchInput;
use OpenApi\Attributes as OA;

class ShowCategoryBranchController
{
    #[OA\Get(
        path: '/api/v1/categories/{categoryId}/branch',
        operationId: 'categoriesShowBranch',
        summary: 'Получить ветку категории с детьми и внуками',
        tags: ['Categories'],
        parameters: [
            new OA\Parameter(name: 'categoryId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'only_active', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ветка категории',
                content: new OA\JsonContent(ref: '#/components/schemas/CategoryBranchResponse'),
            ),
            new OA\Response(response: 404, description: 'Категория не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ],
    )]
    public function __invoke(
        ShowCategoryBranchRequest $request,
        ShowCategoryBranchHandler $handler,
    ): ShowCategoryBranchResponse {
        $result = $handler->execute(
            ShowCategoryBranchInput::from([
                'categoryId' => $request->categoryId(),
                'onlyActive' => $request->onlyActive(),
            ]),
        );

        return ShowCategoryBranchResponse::make($result);
    }
}
