<?php

declare(strict_types=1);

namespace App\Catalog\Http\GetCategoryAttributes;

use App\Catalog\Application\UseCases\GetCategoryAttributes\GetCategoryAttributesOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin GetCategoryAttributesOutput
 */
class GetCategoryAttributesResponse extends OutputResource
{
    /**
     * @return array{
     *     category: array<string, mixed>,
     *     items: array<int, array<string, mixed>>
     * }
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
