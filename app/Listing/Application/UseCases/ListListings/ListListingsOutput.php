<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListListings;

use App\Shared\Domain\DTO\Output;

class ListListingsOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function __construct(
        public readonly array $items,
    ) {}
}
