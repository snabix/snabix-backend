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
        $result = $handler->execute(ShowCategoryBranchInput::from($request->inputData()));

        return ShowCategoryBranchResponse::make($result);
    }
}
