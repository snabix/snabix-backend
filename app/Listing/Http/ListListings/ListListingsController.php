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
        $request->validated();

        $result     = $handler->execute(
            ListListingsInput::from([
                'userId'     => $request->userId(),
                'page'       => $request->integer('page', 1),
                'perPage'    => $request->integer('perPage', 12),
                'status'     => $request->nullableIntegerInput('status'),
                'type'       => $request->nullableIntegerInput('type'),
                'categoryId' => $request->nullableIntegerInput('categoryId'),
            ]),
        );

        return ListListingsResponse::make($result);
    }
}
