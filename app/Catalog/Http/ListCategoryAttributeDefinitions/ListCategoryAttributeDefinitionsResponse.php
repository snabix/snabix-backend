<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ListCategoryAttributeDefinitions\ListCategoryAttributeDefinitionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListCategoryAttributeDefinitionsOutput
 */
class ListCategoryAttributeDefinitionsResponse extends JsonResource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return $this->items;
    }
}
