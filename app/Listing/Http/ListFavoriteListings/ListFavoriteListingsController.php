<?php

declare(strict_types=1);

namespace App\Listing\Http\ListFavoriteListings;

use App\Listing\Application\UseCases\ListFavoriteListings\ListFavoriteListingsHandler;
use App\Listing\Application\UseCases\ListFavoriteListings\ListFavoriteListingsInput;

class ListFavoriteListingsController
{
    public function __invoke(
        ListFavoriteListingsRequest $request,
        ListFavoriteListingsHandler $handler,
    ): ListFavoriteListingsResponse {
        $result = $handler->execute(ListFavoriteListingsInput::from($request->inputData()));

        return ListFavoriteListingsResponse::make($result);
    }
}
