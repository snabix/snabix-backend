<?php

declare(strict_types=1);

namespace App\Listing\Http\ShowListing;

use App\Listing\Application\UseCases\ShowListing\ShowListingHandler;
use App\Listing\Application\UseCases\ShowListing\ShowListingInput;

class ShowListingController
{
    public function __invoke(
        ShowListingRequest $request,
        ShowListingHandler $handler,
    ): ShowListingResponse {
        $result = $handler->execute(
            ShowListingInput::from([
                'userId'    => $request->userId(),
                'listingId' => $request->listingId(),
            ]),
        );

        return ShowListingResponse::make($result);
    }
}
