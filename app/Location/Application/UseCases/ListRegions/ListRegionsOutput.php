<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListRegions;

use App\Shared\Domain\DTO\Output;

class ListRegionsOutput extends Output
{
    /**
     * @param list<array<string, mixed>> $regions
     */
    public function __construct(
        public readonly array $regions,
    ) {}
}
