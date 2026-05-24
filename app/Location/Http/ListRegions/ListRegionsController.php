<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use App\Location\Application\UseCases\ListRegions\ListRegionsHandler;
use App\Location\Application\UseCases\ListRegions\ListRegionsInput;

class ListRegionsController
{
    public function __invoke(
        ListRegionsRequest $request,
        ListRegionsHandler $handler,
    ): ListRegionsResponse {
        $result = $handler->execute(ListRegionsInput::from($request->inputData()));

        return ListRegionsResponse::make($result);
    }
}
