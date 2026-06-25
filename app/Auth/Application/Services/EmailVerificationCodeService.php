<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EmailVerificationCodeService
{
    private const int TTL_MINUTES                 = 60;

    private const int RESEND_COOLDOWN_SECONDS     = 60;

    private const int PREVIOUS_CODE_GRACE_SECONDS = 600;

    public function issue(string $userId, string $email): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->store($userId, $email, $code);

        return $code;
    }

    public function reuseOrIssue(string $userId, string $email): string
    {
        $payload = Cache::get($this->cacheKey($userId));

        if (is_array($payload) && ($payload['email'] ?? null) === $email) {
            $encryptedCode    = $payload['encryptedCode'] ?? null;

            if (is_string($encryptedCode)) {
                try {
                    $code = Crypt::decryptString($encryptedCode);

                    if (preg_match('/^\d{6}$/', $code) === 1) {
                        $this->store($userId, $email, $code);

                        return $code;
                    }
                } catch (DecryptException) {
                    // A code encrypted with an old application key must be replaced.
                }
            }

            $previousCodeHash = $payload['codeHash'] ?? null;
            $code             = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $this->store(
                $userId,
                $email,
                $code,
                is_string($previousCodeHash) ? $previousCodeHash : null,
            );

            return $code;
        }

        return $this->issue($userId, $email);
    }

    public function verify(string $userId, string $email, string $code): bool
    {
        $payload                = Cache::get($this->cacheKey($userId));

        if (! is_array($payload)) {
            return false;
        }

        $codeHash               = $payload['codeHash'] ?? null;
        $storedEmail            = $payload['email'] ?? null;

        if (! is_string($codeHash) || ! is_string($storedEmail)) {
            return false;
        }

        if (! hash_equals($storedEmail, $email)) {
            return false;
        }

        if (hash_equals(
            $codeHash,
            $this->hash($userId, $email, $code),
        )) {
            return true;
        }

        $previousCodeHash       = $payload['previousCodeHash'] ?? null;
        $previousCodeValidUntil = $payload['previousCodeValidUntil'] ?? null;

        return is_string($previousCodeHash)
            && is_int($previousCodeValidUntil)
            && $previousCodeValidUntil >= now()->timestamp
            && hash_equals($previousCodeHash, $this->hash($userId, $email, $code));
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

    private function store(
        string $userId,
        string $email,
        string $code,
        ?string $previousCodeHash = null,
    ): void {
        $payload = [
            'codeHash'      => $this->hash($userId, $email, $code),
            'encryptedCode' => Crypt::encryptString($code),
            'email'         => $email,
            'lastSentAt'    => now()->timestamp,
            'cooldownUntil' => now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp,
        ];

        if ($previousCodeHash !== null) {
            $payload['previousCodeHash']       = $previousCodeHash;
            $payload['previousCodeValidUntil'] = now()->addSeconds(self::PREVIOUS_CODE_GRACE_SECONDS)->timestamp;
        }

        Cache::put(
            $this->cacheKey($userId),
            $payload,
            now()->addMinutes(self::TTL_MINUTES),
        );
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
