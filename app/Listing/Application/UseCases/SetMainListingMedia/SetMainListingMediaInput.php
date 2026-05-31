<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\SetMainListingMedia;

use App\Shared\Domain\DTO\Input;

class SetMainListingMediaInput extends Input
{
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
        public readonly int $mediaId,
    ) {}
}
