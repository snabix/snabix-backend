<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListCities;

use App\Shared\Domain\DTO\Output;

class ListCitiesOutput extends Output
{
    /**
     * @param list<array<string, mixed>> $cities
     */
    public function __construct(
        public readonly array $cities,
    ) {}
}
