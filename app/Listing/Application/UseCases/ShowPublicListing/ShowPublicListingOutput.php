<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\ShowPublicListing;

use App\Shared\Domain\DTO\Output;

class ShowPublicListingOutput extends Output
{
    /** @var array<string, mixed> */
    public array $item;
}
