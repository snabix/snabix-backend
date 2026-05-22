<?php

declare(strict_types=1);

namespace App\Listing\Http\ListPublicListings;

use App\Listing\Application\UseCases\ListPublicListings\ListPublicListingsHandler;
use App\Listing\Application\UseCases\ListPublicListings\ListPublicListingsInput;

class ListPublicListingsController
{
    public function __invoke(
        ListPublicListingsRequest $request,
        ListPublicListingsHandler $handler,
    ): ListPublicListingsResponse {
        $result = $handler->execute(ListPublicListingsInput::from($request->inputData()));

        return ListPublicListingsResponse::make($result);
    }
}
