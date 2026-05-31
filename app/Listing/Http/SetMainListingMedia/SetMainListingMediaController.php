<?php

declare(strict_types=1);

namespace App\Listing\Http\SetMainListingMedia;

use App\Listing\Application\UseCases\SetMainListingMedia\SetMainListingMediaHandler;
use App\Listing\Application\UseCases\SetMainListingMedia\SetMainListingMediaInput;

class SetMainListingMediaController
{
    public function __invoke(
        SetMainListingMediaRequest $request,
        SetMainListingMediaHandler $handler,
    ): SetMainListingMediaResponse {
        $result = $handler->execute(SetMainListingMediaInput::from($request->inputData()));

        return SetMainListingMediaResponse::make($result);
    }
}
