<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListPublicListings;

use App\Shared\Domain\DTO\Input;

class ListPublicListingsInput extends Input
{
    public function __construct(
        public readonly int $limit = 24,
    ) {}
}
