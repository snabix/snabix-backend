<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\GetCategoryAttributes;

use App\Shared\Domain\DTO\Input;

class GetCategoryAttributesInput extends Input
{
    public function __construct(
        public readonly int $categoryId,
        public readonly bool $onlyActive,
    ) {}
}
