<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UpdateListing;

use App\Shared\Domain\DTO\Output;

class UpdateListingOutput extends Output
{
    /**
     * @param array<string, mixed> $item
     */
    public function __construct(
        public readonly array $item,
    ) {}
}
