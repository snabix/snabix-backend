<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowPublicListing;

use App\Shared\Domain\DTO\Input;

class ShowPublicListingInput extends Input
{
    public string $listingId;
}
