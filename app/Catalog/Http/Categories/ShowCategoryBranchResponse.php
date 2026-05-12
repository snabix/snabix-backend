<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin ShowCategoryBranchOutput
 */
#[OA\Schema(
    schema: 'CategoryBranchResponse',
    properties: [
        new OA\Property(
            property: 'data',
            ref: '#/components/schemas/CategoryNode',
        ),
    ],
    type: 'object',
)]
class ShowCategoryBranchResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
