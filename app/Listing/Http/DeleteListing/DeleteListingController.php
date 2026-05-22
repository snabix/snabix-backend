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
        $result = $handler->execute(
            DeleteListingInput::from([
                'userId'    => $request->userId(),
                'listingId' => $request->listingId(),
            ]),
        );

        return DeleteListingResponse::make($result);
    }
}
