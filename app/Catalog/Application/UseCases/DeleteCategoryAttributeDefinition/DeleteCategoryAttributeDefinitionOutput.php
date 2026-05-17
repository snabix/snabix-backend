<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\DeleteCategoryAttributeDefinition;

use App\Shared\Domain\DTO\Output;

class DeleteCategoryAttributeDefinitionOutput extends Output
{
    public function __construct(
        public readonly bool $deleted,
    ) {}
}
