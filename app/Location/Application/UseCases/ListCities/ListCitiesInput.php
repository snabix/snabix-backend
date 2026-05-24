<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListCities;

use App\Shared\Domain\DTO\Input;

class ListCitiesInput extends Input
{
    public function __construct(
        public readonly int $regionId,
        public readonly ?string $search,
    ) {}
}
