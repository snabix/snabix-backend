<?php

declare(strict_types=1);

namespace App\Catalog\Http\ExportCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions\ExportCategoryAttributeDefinitionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExportCategoryAttributeDefinitionsOutput
 */
class ExportCategoryAttributeDefinitionsResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->items,
            'meta'  => $this->meta,
        ];
    }
}
