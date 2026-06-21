<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryBranch;

use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ShowCategoryBranchOutput
 */
class ShowCategoryBranchResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
