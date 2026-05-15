<?php

declare(strict_types=1);

namespace App\Catalog\Http\DeleteCategoryAttributeDefinition;

use App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition\DeleteCategoryAttributeDefinitionOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeleteCategoryAttributeDefinitionOutput
 */
class DeleteCategoryAttributeDefinitionResponse extends JsonResource
{
    /**
     * @return array{deleted: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'deleted' => (bool) $this->deleted,
        ];
    }
}
