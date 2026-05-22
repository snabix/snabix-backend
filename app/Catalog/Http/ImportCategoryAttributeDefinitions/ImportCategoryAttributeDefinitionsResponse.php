<?php

declare(strict_types=1);

namespace App\Catalog\Http\ImportCategoryAttributeDefinitions;

use App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions\ImportCategoryAttributeDefinitionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ImportCategoryAttributeDefinitionsOutput
 */
class ImportCategoryAttributeDefinitionsResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'items'   => $this->items,
        ];
    }
}
