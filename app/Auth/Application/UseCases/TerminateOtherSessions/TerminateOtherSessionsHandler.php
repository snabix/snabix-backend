<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\TerminateOtherSessions;

use App\Auth\Infrastructure\Models\EloquentSession;

class TerminateOtherSessionsHandler
{
    public function execute(TerminateOtherSessionsInput $data): TerminateOtherSessionsOutput
    {
        $query           = EloquentSession::query()
            ->where('user_id', $data->userId);

        if ($data->currentSessionId !== null) {
            $query->where('id', '!=', $data->currentSessionId);
        }

        $terminatedCount = $query->delete();

        return TerminateOtherSessionsOutput::from([
            'terminated'      => true,
            'terminatedCount' => $terminatedCount,
        ]);
    }
}
