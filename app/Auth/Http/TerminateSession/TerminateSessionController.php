<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateSession;

use App\Auth\Application\UseCases\TerminateSession\TerminateSessionHandler;
use App\Auth\Application\UseCases\TerminateSession\TerminateSessionInput;

class TerminateSessionController
{
    public function __invoke(
        TerminateSessionRequest $request,
        TerminateSessionHandler $handler,
    ): TerminateSessionResponse {
        $result = $handler->execute(TerminateSessionInput::from($request->inputData()));

        return TerminateSessionResponse::make($result);
    }
}
