<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowListing;

use App\Shared\Domain\DTO\Output;

class ShowListingOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
