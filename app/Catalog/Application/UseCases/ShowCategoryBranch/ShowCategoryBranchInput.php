<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ShowCategoryBranch;

use App\Shared\Domain\DTO\Input;

class ShowCategoryBranchInput extends Input
{
    public function __construct(
        public readonly string $categoryId,
        public readonly bool $onlyActive,
    ) {}
}
