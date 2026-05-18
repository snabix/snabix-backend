<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListListings;

use App\Shared\Domain\DTO\Input;

class ListListingsInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly int $page = 1,
        public readonly int $perPage = 12,
        public readonly ?int $status = null,
        public readonly ?int $type = null,
        public readonly ?int $categoryId = null,
    ) {}
}
