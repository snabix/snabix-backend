<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Shared\Domain\Enums\SystemLogLevel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class AbuseEventLogger
{
    public function __construct(
        private SystemLogManager $systemLogManager,
    ) {}

    public function emailVerificationRequired(Request $request, string $action): void
    {
        $this->blocked(
            request: $request,
            action: $action,
            reason: 'email_verification_required',
        );
    }

    public function rateLimitExceeded(
        Request $request,
        string $action,
        string $scope,
        string $dimension,
        int $retryAfterSeconds,
    ): void {
        $this->blocked(
            request: $request,
            action: $action,
            reason: 'rate_limit_exceeded',
            context: [
                'scope'               => $scope,
                'dimension'           => $dimension,
                'retry_after_seconds' => $retryAfterSeconds,
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function blocked(
        Request $request,
        string $action,
        string $reason,
        array $context = [],
    ): void {
        $route     = $request->route();
        $routeName = $route?->getName();

        $this->systemLogManager->log(
            level: SystemLogLevel::WARNING,
            category: 'abuse',
            message: 'Marketplace action blocked by abuse protection policy.',
            action: $action,
            context: [
                'reason'         => $reason,
                'policy_version' => 1,
                ...$context,
            ],
            routeName: is_string($routeName) ? $routeName : null,
            method: $request->method(),
            path: '/' . ltrim($request->path(), '/'),
            statusCode: $reason === 'rate_limit_exceeded' ? 429 : 403,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            userId: $this->userId($request),
        );
    }

    private function userId(Request $request): ?string
    {
        $user       = $request->user();

        if (! $user instanceof Authenticatable) {
            return null;
        }

        $identifier = $user->getAuthIdentifier();

        if (! is_string($identifier) && ! is_int($identifier)) {
            return null;
        }

        $userId     = (string) $identifier;

        return Str::isUuid($userId) ? $userId : null;
    }
}
