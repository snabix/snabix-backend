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
        $request->validated();

        $result    = $handler->execute(
            ListPublicListingsInput::from([
                'page'    => $request->integer('page', 1),
                'perPage' => $request->integer('perPage', 24),
            ]),
        );

        return ListPublicListingsResponse::make($result);
    }
}
