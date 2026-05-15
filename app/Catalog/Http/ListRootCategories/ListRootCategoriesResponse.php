<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListRootCategories;

use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListRootCategoriesOutput
 */
class ListRootCategoriesResponse extends JsonResource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return $this->items;
    }
}
