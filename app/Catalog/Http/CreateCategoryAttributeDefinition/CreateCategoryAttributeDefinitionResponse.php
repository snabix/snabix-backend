<?php

declare(strict_types=1);

namespace App\Catalog\Http\CreateCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\CreateCategoryAttributeDefinition\CreateCategoryAttributeDefinitionOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CreateCategoryAttributeDefinitionOutput
 */
class CreateCategoryAttributeDefinitionResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
