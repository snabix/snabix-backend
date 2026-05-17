<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListing;

use App\Listing\Application\UseCases\DeleteListing\DeleteListingHandler;
use App\Listing\Application\UseCases\DeleteListing\DeleteListingInput;

class DeleteListingController
{
    public function __invoke(
        DeleteListingRequest $request,
        DeleteListingHandler $handler,
    ): DeleteListingResponse {
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
        $listingId  = $request->route('listingId');

        $result     = $handler->execute(
            DeleteListingInput::from([
                'userId'    => $userId,
                'listingId' => is_string($listingId) ? $listingId : '',
            ]),
        );

        return DeleteListingResponse::make($result);
    }
}
