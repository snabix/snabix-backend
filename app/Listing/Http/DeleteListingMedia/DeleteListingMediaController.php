<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListingMedia;

use App\Listing\Application\UseCases\DeleteListingMedia\DeleteListingMediaHandler;
use App\Listing\Application\UseCases\DeleteListingMedia\DeleteListingMediaInput;

class DeleteListingMediaController
{
    public function __invoke(
        DeleteListingMediaRequest $request,
        DeleteListingMediaHandler $handler,
    ): DeleteListingMediaResponse {
        $result = $handler->execute(DeleteListingMediaInput::from($request->inputData()));

        return DeleteListingMediaResponse::make($result);
    }
}
