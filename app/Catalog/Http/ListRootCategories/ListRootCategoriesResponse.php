<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListRootCategories;

use App\Catalog\Application\UseCases\ListRootCategories\ListRootCategoriesOutput;
use App\Shared\Http\Resources\ItemsOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListRootCategoriesOutput
 */
class ListRootCategoriesResponse extends ItemsOutputResource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
