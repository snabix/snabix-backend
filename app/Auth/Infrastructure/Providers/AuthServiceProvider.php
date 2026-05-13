<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('auth.sign-in', function (Request $request): Limit {
            $email = (string) $request->string('email')->lower()->trim();
            $ip    = $request->ip() ?? 'unknown';

            return Limit::perMinute(5)
                ->by($email !== '' ? $email . '|' . $ip : $ip)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много попыток входа. Попробуйте немного позже.',
                ], 429));
        });

        RateLimiter::for('auth.sign-up', function (Request $request): Limit {
            $ip = $request->ip() ?? 'unknown';

            return Limit::perHour(10)
                ->by($ip)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много попыток регистрации. Попробуйте позже.',
                ], 429));
        });

        RateLimiter::for('auth.forgot-password', function (Request $request): Limit {
            $email = (string) $request->string('email')->lower()->trim();
            $ip    = $request->ip() ?? 'unknown';

            return Limit::perMinutes(15, 3)
                ->by($email !== '' ? $email . '|' . $ip : $ip)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много запросов на восстановление пароля. Попробуйте позже.',
                ], 429));
        });

        RateLimiter::for('auth.reset-password', function (Request $request): Limit {
            $email = (string) $request->string('email')->lower()->trim();
            $ip    = $request->ip() ?? 'unknown';

            return Limit::perMinutes(15, 5)
                ->by($email !== '' ? $email . '|' . $ip : $ip)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много попыток сброса пароля. Попробуйте позже.',
                ], 429));
        });

        RateLimiter::for('auth.verify-email', function (Request $request): Limit {
            $user = $request->user();
            $userId = is_object($user) && (is_string($user->getAuthIdentifier()) || is_int($user->getAuthIdentifier()))
                ? (string) $user->getAuthIdentifier()
                : '';
            $ip = $request->ip() ?? 'unknown';

            return Limit::perMinutes(15, 5)
                ->by($userId !== '' ? $userId . '|' . $ip : $ip)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много попыток подтверждения email. Попробуйте позже.',
                ], 429));
        });

        RateLimiter::for('auth.resend-verification', function (Request $request): Limit {
            $user = $request->user();
            $userId = is_object($user) && (is_string($user->getAuthIdentifier()) || is_int($user->getAuthIdentifier()))
                ? (string) $user->getAuthIdentifier()
                : ($request->ip() ?? 'unknown');

            return Limit::perMinutes(15, 5)
                ->by($userId)
                ->response(fn(): JsonResponse => response()->json([
                    'message' => 'Слишком много запросов на повторную отправку кода. Попробуйте позже.',
                ], 429));
        });
    }
}
