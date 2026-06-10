<?php

declare(strict_types=1);

namespace App\Listing\Http\ArchiveListing;

use App\Listing\Application\UseCases\ArchiveListing\ArchiveListingHandler;
use App\Listing\Application\UseCases\ArchiveListing\ArchiveListingInput;

class ArchiveListingController
{
    public function __invoke(
        ArchiveListingRequest $request,
        ArchiveListingHandler $handler,
    ): ArchiveListingResponse {
        $result = $handler->execute(ArchiveListingInput::from($request->inputData()));

        return ArchiveListingResponse::make($result);
    }
}
