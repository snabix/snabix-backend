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
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
        $listingId  = $request->route('listingId');

        $result     = $handler->execute(
            ShowListingInput::from([
                'userId'    => $userId,
                'listingId' => is_string($listingId) ? $listingId : '',
            ]),
        );

        return ShowListingResponse::make($result);
    }
}
