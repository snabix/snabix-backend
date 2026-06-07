<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\DeleteListing;

use App\Shared\Domain\DTO\Input;

class DeleteListingInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
    ) {}
}
