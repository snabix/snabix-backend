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
        $user       = $request->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;
        $userId     = is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';

        $result     = $handler->execute(
            ListListingsInput::from([
                'userId' => $userId,
            ]),
        );

        return ListListingsResponse::make($result);
    }
}
