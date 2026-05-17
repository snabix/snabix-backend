<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\DeleteListing;

use App\Shared\Domain\DTO\Output;

class DeleteListingOutput extends Output
{
    public function __construct(
        public readonly bool $deleted,
    ) {}
}
