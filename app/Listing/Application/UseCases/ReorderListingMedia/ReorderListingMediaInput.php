<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ReorderListingMedia;

use App\Shared\Domain\DTO\Input;

class ReorderListingMediaInput extends Input
{
    /**
     * @param list<int> $mediaIds
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
        public readonly array $mediaIds,
    ) {}
}
