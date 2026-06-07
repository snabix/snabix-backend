<?php

declare(strict_types=1);

namespace App\Listing\Http\AddListingFavorite;

use App\Listing\Application\UseCases\AddListingFavorite\AddListingFavoriteHandler;
use App\Listing\Application\UseCases\AddListingFavorite\AddListingFavoriteInput;

class AddListingFavoriteController
{
    public function __invoke(
        AddListingFavoriteRequest $request,
        AddListingFavoriteHandler $handler,
    ): AddListingFavoriteResponse {
        $result = $handler->execute(AddListingFavoriteInput::from($request->inputData()));

        return AddListingFavoriteResponse::make($result);
    }
}
