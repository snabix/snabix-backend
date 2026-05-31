<?php

declare(strict_types=1);

namespace App\Listing\Http\RemoveListingFavorite;

use App\Listing\Application\UseCases\RemoveListingFavorite\RemoveListingFavoriteHandler;
use App\Listing\Application\UseCases\RemoveListingFavorite\RemoveListingFavoriteInput;

class RemoveListingFavoriteController
{
    public function __invoke(
        RemoveListingFavoriteRequest $request,
        RemoveListingFavoriteHandler $handler,
    ): RemoveListingFavoriteResponse {
        $result = $handler->execute(RemoveListingFavoriteInput::from($request->inputData()));

        return RemoveListingFavoriteResponse::make($result);
    }
}
