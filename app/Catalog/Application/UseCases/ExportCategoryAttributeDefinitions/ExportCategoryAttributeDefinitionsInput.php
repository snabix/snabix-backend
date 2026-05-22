<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ExportCategoryAttributeDefinitions;

use App\Shared\Domain\DTO\Input;

class ExportCategoryAttributeDefinitionsInput extends Input
{
    public function __construct(
        public readonly bool $onlyActive = false,
    ) {}
}
