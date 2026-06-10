<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ArchiveListing;

use App\Shared\Domain\DTO\Input;

class ArchiveListingInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
    ) {}
}
