<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryBranch;

use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShowCategoryBranchOutput
 */
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
