<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ListPublicListings;

use App\Shared\Domain\DTO\Input;

class ListPublicListingsInput extends Input
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 15,
        public readonly ?string $categoryId = null,
        public readonly ?int $type = null,
        public readonly ?int $minPrice = null,
        public readonly ?int $maxPrice = null,
        public readonly ?int $regionId = null,
        public readonly ?int $cityId = null,
        public readonly ?string $regionQuery = null,
        public readonly ?string $cityQuery = null,
        public readonly ?bool $isNegotiable = null,
        public readonly string $sort = 'newest',
    ) {}
}
