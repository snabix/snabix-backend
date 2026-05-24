<?php

declare(strict_types=1);

namespace App\Listing\Http\UploadListingMedia;

use App\Listing\Application\UseCases\UploadListingMedia\UploadListingMediaHandler;
use App\Listing\Application\UseCases\UploadListingMedia\UploadListingMediaInput;

class UploadListingMediaController
{
    public function __invoke(
        UploadListingMediaRequest $request,
        UploadListingMediaHandler $handler,
    ): UploadListingMediaResponse {
        $result = $handler->execute(UploadListingMediaInput::from($request->inputData()));

        return UploadListingMediaResponse::make($result);
    }
}
