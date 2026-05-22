<?php

declare(strict_types=1);

namespace App\Listing\Http\CreateListing;

use App\Listing\Application\UseCases\CreateListing\CreateListingHandler;
use App\Listing\Application\UseCases\CreateListing\CreateListingInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateListingController
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        CreateListingRequest $request,
        CreateListingHandler $handler,
    ): JsonResponse {
        $result = $handler->execute(CreateListingInput::from($request->inputData()));

        return CreateListingResponse::make($result)
            ->response()
            ->setStatusCode(201);
    }
}
