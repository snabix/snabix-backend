<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

use Illuminate\Support\Facades\Cache;

class EmailVerificationCodeService
{
    private const int TTL_MINUTES             = 60;

    private const int RESEND_COOLDOWN_SECONDS = 60;

    public function issue(string $userId, string $email): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            $this->cacheKey($userId),
            [
                'codeHash'      => $this->hash($userId, $email, $code),
                'email'         => $email,
                'lastSentAt'    => now()->timestamp,
                'cooldownUntil' => now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp,
            ],
            now()->addMinutes(self::TTL_MINUTES),
        );

        return $code;
    }

    public function verify(string $userId, string $email, string $code): bool
    {
        $payload     = Cache::get($this->cacheKey($userId));

        if (! is_array($payload)) {
            return false;
        }

        $codeHash    = $payload['codeHash'] ?? null;
        $storedEmail = $payload['email'] ?? null;

        if (! is_string($codeHash) || ! is_string($storedEmail)) {
            return false;
        }

        if (! hash_equals($storedEmail, $email)) {
            return false;
        }

        return hash_equals(
            $codeHash,
            $this->hash($userId, $email, $code),
        );
    }

    public function forget(string $userId): void
    {
        Cache::forget($this->cacheKey($userId));
    }

    public function resendCooldownSeconds(string $userId): int
    {
        $payload       = Cache::get($this->cacheKey($userId));

        if (! is_array($payload)) {
            return 0;
        }

        $cooldownUntil = $payload['cooldownUntil'] ?? null;

        if (is_int($cooldownUntil)) {
            $cooldownUntilTimestamp = $cooldownUntil;
        } elseif (is_string($cooldownUntil) && ctype_digit($cooldownUntil)) {
            $cooldownUntilTimestamp = (int) $cooldownUntil;
        } else {
            return 0;
        }

        return max(0, $cooldownUntilTimestamp - now()->getTimestamp());
    }

    public function resendCooldownSecondsValue(): int
    {
        return self::RESEND_COOLDOWN_SECONDS;
    }

    public function expiresInMinutes(): int
    {
        return self::TTL_MINUTES;
    }

    private function cacheKey(string $userId): string
    {
        return 'auth:verify-email:code:' . $userId;
    }

    private function hash(string $userId, string $email, string $code): string
    {
        $appKey = config('app.key');

        return hash_hmac(
            'sha256',
            $userId . '|' . mb_strtolower($email) . '|' . $code,
            is_string($appKey) ? $appKey : '',
        );
    }
}
