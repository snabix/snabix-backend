<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ListCategories;

use App\Shared\Domain\DTO\Output;

class ListCategoriesOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly array $items,
    ) {}
}
