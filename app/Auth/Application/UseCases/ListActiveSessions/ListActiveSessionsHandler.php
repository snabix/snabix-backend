<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListActiveSessions;

use App\Auth\Application\Services\SessionClientInfo;
use App\Auth\Infrastructure\Models\EloquentSession;

class ListActiveSessionsHandler
{
    public function __construct(
        private readonly SessionClientInfo $clientInfo,
    ) {}

    public function execute(ListActiveSessionsInput $data): ListActiveSessionsOutput
    {
        $sessions = EloquentSession::query()
            ->where('user_id', $data->userId)
            ->orderByRaw('id = ? desc', [$data->currentSessionId ?? ''])
            ->orderByDesc('last_activity')
            ->get();

        return ListActiveSessionsOutput::from([
            'items' => $sessions
                ->map(fn(EloquentSession $session): array => $this->mapSession($session, $data->currentSessionId))
                ->all(),
        ]);
    }

    /**
     * @return array{
     *     id: string,
     *     deviceName: string,
     *     browser: string,
     *     ipAddress: ?string,
     *     locationLabel: string,
     *     type: string,
     *     isCurrent: bool,
     *     lastActivityAt: ?string
     * }
     */
    private function mapSession(EloquentSession $session, ?string $currentSessionId): array
    {
        $userAgent = $session->user_agent ?? '';

        return [
            'id'             => $session->id,
            'deviceName'     => $this->clientInfo->deviceName($userAgent),
            'browser'        => $this->clientInfo->browser($userAgent),
            'ipAddress'      => $session->ip_address,
            'locationLabel'  => $this->clientInfo->locationLabel($session->ip_address),
            'type'           => $this->clientInfo->deviceType($userAgent),
            'isCurrent'      => $currentSessionId !== null && hash_equals($session->id, $currentSessionId),
            'lastActivityAt' => $session->last_activity > 0
                ? $session->lastActivityAt()->toISOString()
                : null,
        ];
    }
}
