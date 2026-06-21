<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Listing\Infrastructure\Models\EloquentListing;

final readonly class PublicListingPayloadMapper
{
    public function __construct(
        private ListingPayloadAssembler $assembler,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function map(EloquentListing $listing): array
    {
        return $this->assembler->assemble(
            $listing,
            ListingPayloadVisibility::PUBLIC_VIEW,
        );
    }
}
