<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListFavoriteListings;

use App\Shared\Domain\DTO\Input;

class ListFavoriteListingsInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly int $page,
        public readonly int $perPage,
    ) {}
}
