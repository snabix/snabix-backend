<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use App\Catalog\Application\UseCases\ListCategories\ListCategoriesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ListCategoriesOutput
 */
#[OA\Schema(
    schema: 'CategoryNode',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'parentId', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'name', type: 'string', example: 'Электроника'),
        new OA\Property(property: 'slug', type: 'string', example: 'elektronika'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Товары категории электроника'),
        new OA\Property(property: 'sortOrder', type: 'integer', example: 0),
        new OA\Property(property: 'isActive', type: 'boolean', example: true),
        new OA\Property(property: 'path', type: 'string', nullable: true, example: 'elektronika/smartfony'),
        new OA\Property(property: 'depth', type: 'integer', example: 1),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CategoryNode'),
        ),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'CategoriesResponse',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CategoryNode'),
        ),
    ],
    type: 'object',
)]
class ListCategoriesResponse extends JsonResource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return $this->items;
    }
}
