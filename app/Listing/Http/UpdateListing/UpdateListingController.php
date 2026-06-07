<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Application\UseCases\UpdateListing\UpdateListingHandler;
use App\Listing\Application\UseCases\UpdateListing\UpdateListingInput;
use Illuminate\Validation\ValidationException;

class UpdateListingController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        UpdateListingRequest $request,
        UpdateListingHandler $handler,
    ): UpdateListingResponse {
        $result = $handler->execute(UpdateListingInput::from($request->inputData()));

        return UpdateListingResponse::make($result);
    }
}
