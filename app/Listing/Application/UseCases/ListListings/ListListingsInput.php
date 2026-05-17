<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListListings;

use App\Shared\Domain\DTO\Input;

class ListListingsInput extends Input
{
    public function __construct(
        public readonly string $userId,
    ) {}
}
