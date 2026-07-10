<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

readonly class SessionClientInfo
{
    public function deviceType(string $userAgent): string
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

    public function deviceName(string $userAgent): string
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

    public function browser(string $userAgent): string
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

    public function locationLabel(?string $ipAddress): string
    {
        if ($ipAddress === null || $ipAddress === '') {
            return 'Местоположение неизвестно';
        }

        if (in_array($ipAddress, ['127.0.0.1', '::1'], true)) {
            return 'Локальная сеть';
        }

        if (
            str_starts_with($ipAddress, '10.')
            || str_starts_with($ipAddress, '192.168.')
            || preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $ipAddress) === 1
        ) {
            return 'Частная сеть';
        }

        return 'По IP: ' . $ipAddress;
    }
}
