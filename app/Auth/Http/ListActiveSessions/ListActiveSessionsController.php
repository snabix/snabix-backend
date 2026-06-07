<?php

declare(strict_types=1);

namespace App\Auth\Http\ListActiveSessions;

use App\Auth\Application\UseCases\ListActiveSessions\ListActiveSessionsHandler;
use App\Auth\Application\UseCases\ListActiveSessions\ListActiveSessionsInput;

class ListActiveSessionsController
{
    public function __invoke(
        ListActiveSessionsRequest $request,
        ListActiveSessionsHandler $handler,
    ): ListActiveSessionsResponse {
        $result = $handler->execute(ListActiveSessionsInput::from($request->inputData()));

        return ListActiveSessionsResponse::make($result);
    }
}
