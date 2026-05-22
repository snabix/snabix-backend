<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions;

use App\Shared\Domain\DTO\Output;

class ImportCategoryAttributeDefinitionsOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly int $created,
        public readonly int $updated,
        public readonly array $items,
    ) {}
}
