<?php

declare(strict_types=1);

namespace App\Listing\Http\ReorderListingMedia;

use App\Listing\Application\UseCases\ReorderListingMedia\ReorderListingMediaHandler;
use App\Listing\Application\UseCases\ReorderListingMedia\ReorderListingMediaInput;

class ReorderListingMediaController
{
    public function __invoke(
        ReorderListingMediaRequest $request,
        ReorderListingMediaHandler $handler,
    ): ReorderListingMediaResponse {
        $result = $handler->execute(ReorderListingMediaInput::from($request->inputData()));

        return ReorderListingMediaResponse::make($result);
    }
}
