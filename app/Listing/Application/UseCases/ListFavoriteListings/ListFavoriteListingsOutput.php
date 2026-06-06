<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListFavoriteListings;

use App\Shared\Domain\DTO\Output;

class ListFavoriteListingsOutput extends Output
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, int>               $meta
     */
    public function __construct(
        public readonly array $items,
        public readonly array $meta,
    ) {}
}
