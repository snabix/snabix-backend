<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\RemoveListingFavorite;

use App\Listing\Application\Services\ListingFavoriteService;
use App\Listing\Application\Support\PublicListingPayloadMapper;

readonly class RemoveListingFavoriteHandler
{
    public function __construct(
        private ListingFavoriteService $listingFavoriteService,
        private PublicListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(RemoveListingFavoriteInput $input): RemoveListingFavoriteOutput
    {
        $listing = $this->listingFavoriteService->remove($input->userId, $input->listingId);

        return RemoveListingFavoriteOutput::from([
            'item' => [
                ...$this->listingPayloadMapper->map($listing),
                'isFavorite' => false,
            ],
        ]);
    }
}
