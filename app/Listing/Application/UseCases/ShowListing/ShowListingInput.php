<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowListing;

use App\Shared\Domain\DTO\Input;

class ShowListingInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
    ) {}
}
