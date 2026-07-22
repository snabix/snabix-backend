<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Middleware;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Infrastructure\Services\AbuseEventLogger;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class EnsureMarketplaceEmailIsVerified
{
    public function __construct(
        private AbuseEventLogger $abuseEventLogger,
    ) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user     = $request->user();

        if (! $user instanceof EloquentUser) {
            throw new AuthenticationException();
        }

        if ($user->email_verified_at === null) {
            $this->abuseEventLogger->emailVerificationRequired($request, $action);

            return new JsonResponse([
                'message'              => 'Подтвердите email, чтобы выполнить это действие.',
                'code'                 => 'auth.email-verification-required',
                'verificationRequired' => true,
            ], 403);
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
