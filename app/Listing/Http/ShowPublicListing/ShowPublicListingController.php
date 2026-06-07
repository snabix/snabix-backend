<?php

declare(strict_types=1);

namespace App\Listing\Http\ShowPublicListing;

use App\Listing\Application\UseCases\ShowPublicListing\ShowPublicListingHandler;
use App\Listing\Application\UseCases\ShowPublicListing\ShowPublicListingInput;

class ShowPublicListingController
{
    public function __invoke(
        string $listingId,
        ShowPublicListingHandler $handler,
    ): ShowPublicListingResponse {
        $result = $handler->execute(ShowPublicListingInput::from([
            'listingId' => $listingId,
        ]));

        return ShowPublicListingResponse::make($result);
    }
}
