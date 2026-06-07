<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Application\UseCases\ListListings\ListListingsHandler;
use App\Listing\Application\UseCases\ListListings\ListListingsInput;

class ListListingsController
{
    public function __invoke(
        ListListingsRequest $request,
        ListListingsHandler $handler,
    ): ListListingsResponse {
        $result = $handler->execute(ListListingsInput::from($request->inputData()));

        return ListListingsResponse::make($result);
    }
}
