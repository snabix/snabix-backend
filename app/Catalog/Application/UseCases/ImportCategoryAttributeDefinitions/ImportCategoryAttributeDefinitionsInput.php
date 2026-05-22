<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions;

use App\Shared\Domain\DTO\Input;

class ImportCategoryAttributeDefinitionsInput extends Input
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly array $items,
    ) {}
}
