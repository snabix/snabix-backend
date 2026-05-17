<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategories;

use App\Catalog\Application\UseCases\ListCategories\ListCategoriesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListCategoriesOutput
 */
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
