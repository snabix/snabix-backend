<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\ShowCategoryAttributeDefinition\ShowCategoryAttributeDefinitionOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShowCategoryAttributeDefinitionOutput
 */
class ShowCategoryAttributeDefinitionResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
