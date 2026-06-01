<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateOtherSessions;

use App\Auth\Application\UseCases\TerminateOtherSessions\TerminateOtherSessionsHandler;
use App\Auth\Application\UseCases\TerminateOtherSessions\TerminateOtherSessionsInput;

class TerminateOtherSessionsController
{
    public function __invoke(
        TerminateOtherSessionsRequest $request,
        TerminateOtherSessionsHandler $handler,
    ): TerminateOtherSessionsResponse {
        $result = $handler->execute(TerminateOtherSessionsInput::from($request->inputData()));

        return TerminateOtherSessionsResponse::make($result);
    }
}
