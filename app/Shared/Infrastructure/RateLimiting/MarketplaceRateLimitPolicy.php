<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\RateLimiting;

use App\Shared\Infrastructure\Services\AbuseEventLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

readonly class MarketplaceRateLimitPolicy
{
    public function __construct(
        private AbuseEventLogger $abuseEventLogger,
    ) {}

    /**
     * @return array<int, Limit>
     */
    public function publicLimits(Request $request, string $scope, string $action): array
    {
        return [
            $this->limit($request, $scope, $action, 'ip', $this->ipAddress($request)),
        ];
    }

    /**
     * @return array<int, Limit>
     */
    public function authenticatedLimits(Request $request, string $scope, string $action): array
    {
        return [
            $this->limit($request, $scope, $action, 'user', $this->userIdentifier($request)),
            $this->limit($request, $scope, $action, 'ip', $this->ipAddress($request)),
        ];
    }

    private function limit(
        Request $request,
        string $scope,
        string $action,
        string $dimension,
        string $identifier,
    ): Limit {
        $attempts     = $this->positiveInteger($scope, $dimension, 'attempts');
        $decaySeconds = $this->positiveInteger($scope, $dimension, 'decay_seconds');

        return (new Limit('', $attempts, $decaySeconds))
            ->by($scope . ':' . $dimension . ':' . $identifier)
            ->response(function (Request $blockedRequest, array $headers) use (
                $action,
                $scope,
                $dimension,
            ): JsonResponse {
                $retryAfterSeconds = $this->retryAfterSeconds($headers);

                $this->abuseEventLogger->rateLimitExceeded(
                    request: $blockedRequest,
                    action: $action,
                    scope: $scope,
                    dimension: $dimension,
                    retryAfterSeconds: $retryAfterSeconds,
                );

                return response()->json([
                    'message'           => 'Слишком много запросов. Повторите попытку позже.',
                    'code'              => 'abuse.rate-limit-exceeded',
                    'retryAfterSeconds' => $retryAfterSeconds,
                ], 429, $headers);
            });
    }

    private function positiveInteger(string $scope, string $dimension, string $key): int
    {
        $configKey = sprintf('marketplace-abuse.limits.%s.%s.%s', $scope, $dimension, $key);
        $value     = config($configKey);

        if (! is_int($value) || $value < 1) {
            throw new RuntimeException(sprintf('Marketplace rate limit [%s] must be a positive integer.', $configKey));
        }

        return $value;
    }

    private function userIdentifier(Request $request): string
    {
        $user       = $request->user();

        if (! $user instanceof Authenticatable) {
            return 'guest:' . $this->ipAddress($request);
        }

        $identifier = $user->getAuthIdentifier();

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : 'guest:' . $this->ipAddress($request);
    }

    private function ipAddress(Request $request): string
    {
        return $request->ip() ?? 'unknown';
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function retryAfterSeconds(array $headers): int
    {
        $retryAfter = $headers['Retry-After'] ?? null;

        if (is_int($retryAfter)) {
            return max(1, $retryAfter);
        }

        if (is_string($retryAfter) && ctype_digit($retryAfter)) {
            return max(1, (int) $retryAfter);
        }

        return 1;
    }
}
