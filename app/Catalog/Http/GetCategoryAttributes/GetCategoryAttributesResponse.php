<?php

declare(strict_types=1);

namespace App\Catalog\Http\GetCategoryAttributes;

use App\Catalog\Application\UseCases\GetCategoryAttributes\GetCategoryAttributesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GetCategoryAttributesOutput
 */
class GetCategoryAttributesResponse extends JsonResource
{
    /**
     * @return array{
     *     category: array<string, mixed>,
     *     items: array<int, array<string, mixed>>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'category' => $this->category,
            'items'    => $this->items,
        ];
    }
}
