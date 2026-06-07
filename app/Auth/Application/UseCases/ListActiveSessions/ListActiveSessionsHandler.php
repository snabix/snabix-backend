<?php

declare(strict_types=1);

namespace App\Auth\Application\UseCases\ListActiveSessions;

use App\Auth\Infrastructure\Models\EloquentSession;

class ListActiveSessionsHandler
{
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
            'deviceName'     => $this->detectDeviceName($userAgent),
            'browser'        => $this->detectBrowser($userAgent),
            'ipAddress'      => $session->ip_address,
            'type'           => $this->detectDeviceType($userAgent),
            'isCurrent'      => $currentSessionId !== null && hash_equals($session->id, $currentSessionId),
            'lastActivityAt' => $session->last_activity > 0
                ? $session->lastActivityAt()->toISOString()
                : null,
        ];
    }

    private function detectDeviceType(string $userAgent): string
    {
        $normalized = mb_strtolower($userAgent);

        if (str_contains($normalized, 'ipad') || str_contains($normalized, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($normalized, 'mobile') || str_contains($normalized, 'iphone') || str_contains($normalized, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectDeviceName(string $userAgent): string
    {
        $normalized = mb_strtolower($userAgent);

        return match (true) {
            str_contains($normalized, 'iphone')                                             => 'iPhone',
            str_contains($normalized, 'ipad')                                               => 'iPad',
            str_contains($normalized, 'android')                                            => 'Android устройство',
            str_contains($normalized, 'mac os x') || str_contains($normalized, 'macintosh') => 'macOS устройство',
            str_contains($normalized, 'windows')                                            => 'Windows устройство',
            str_contains($normalized, 'linux')                                              => 'Linux устройство',
            default                                                                         => 'Неизвестное устройство',
        };
    }

    private function detectBrowser(string $userAgent): string
    {
        $normalized = mb_strtolower($userAgent);

        return match (true) {
            str_contains($normalized, 'edg/')                                               => 'Microsoft Edge',
            str_contains($normalized, 'opr/') || str_contains($normalized, 'opera')         => 'Opera',
            str_contains($normalized, 'firefox/')                                           => 'Firefox',
            str_contains($normalized, 'chrome/') && ! str_contains($normalized, 'chromium') => 'Chrome',
            str_contains($normalized, 'safari/')                                            => 'Safari',
            default                                                                         => 'Неизвестный браузер',
        };
    }
}
