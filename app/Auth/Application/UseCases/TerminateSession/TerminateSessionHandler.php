<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateSession;

use App\Auth\Infrastructure\Models\EloquentSession;

class TerminateSessionHandler
{
    public function execute(TerminateSessionInput $data): TerminateSessionOutput
    {
        EloquentSession::query()
            ->where('user_id', $data->userId)
            ->where('id', $data->sessionId)
            ->delete();

        return TerminateSessionOutput::from([
            'terminated' => true,
        ]);
    }
}
