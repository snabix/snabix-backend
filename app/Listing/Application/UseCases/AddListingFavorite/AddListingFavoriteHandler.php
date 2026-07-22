<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\AddListingFavorite;

use App\Listing\Application\Services\ListingFavoriteService;
use App\Listing\Application\Support\PublicListingPayloadMapper;

readonly class AddListingFavoriteHandler
{
    public function __construct(
        private ListingFavoriteService $listingFavoriteService,
        private PublicListingPayloadMapper $listingPayloadMapper,
    ) {}

    public function execute(AddListingFavoriteInput $input): AddListingFavoriteOutput
    {
        $listing = $this->listingFavoriteService->add($input->userId, $input->listingId);

        return AddListingFavoriteOutput::from([
            'item' => [
                ...$this->listingPayloadMapper->map($listing),
                'isFavorite' => true,
            ],
        ]);
    }
}
