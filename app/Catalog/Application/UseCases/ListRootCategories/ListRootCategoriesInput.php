<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListRootCategories;

use App\Shared\Domain\DTO\Input;

class ListRootCategoriesInput extends Input
{
    public function __construct(
        public readonly bool $onlyActive,
    ) {}
}
