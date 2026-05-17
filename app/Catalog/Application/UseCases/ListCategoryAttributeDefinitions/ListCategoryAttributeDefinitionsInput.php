<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListCategoryAttributeDefinitions;

use App\Shared\Domain\DTO\Input;

class ListCategoryAttributeDefinitionsInput extends Input
{
    public function __construct(
        public readonly bool $onlyActive = false,
    ) {}
}
