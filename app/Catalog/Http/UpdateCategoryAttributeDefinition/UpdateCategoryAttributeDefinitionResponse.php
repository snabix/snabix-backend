<?php

declare(strict_types=1);

namespace App\Catalog\Http\UpdateCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\UpdateCategoryAttributeDefinition\UpdateCategoryAttributeDefinitionOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UpdateCategoryAttributeDefinitionOutput
 */
class UpdateCategoryAttributeDefinitionResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
