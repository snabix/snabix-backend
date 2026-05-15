<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryBranch;

use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchHandler;
use App\Catalog\Application\UseCases\ShowCategoryBranch\ShowCategoryBranchInput;

class ShowCategoryBranchController
{
    public function __invoke(
        ShowCategoryBranchRequest $request,
        ShowCategoryBranchHandler $handler,
    ): ShowCategoryBranchResponse {
        $validated = $request->validated();

        $result    = $handler->execute(
            ShowCategoryBranchInput::from([
                'categoryId' => (int) $request->route('categoryId'),
                'onlyActive' => (bool) ($validated['only_active'] ?? true),
            ]),
        );

        return ShowCategoryBranchResponse::make($result);
    }
}
