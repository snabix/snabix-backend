<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListCategories;

use App\Shared\Domain\DTO\Input;

class ListCategoriesInput extends Input
{
    public function __construct(
        public readonly bool $onlyActive,
        public readonly bool $tree,
    ) {}
}
