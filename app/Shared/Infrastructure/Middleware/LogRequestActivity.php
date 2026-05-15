<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Middleware;

use App\Shared\Domain\Enums\SystemLogLevel;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

readonly class LogRequestActivity
{
    public function __construct(
        private SystemLogManager $systemLogManager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt  = microtime(true);
        /** @var Response $response */
        $response   = $next($request);

        if (! (bool) config('system-logging.enabled', true)) {
            return $response;
        }

        if (! (bool) config('system-logging.http_requests_enabled', true)) {
            return $response;
        }

        if (! $this->shouldLog($request)) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        if (! $this->shouldPersist($request, $statusCode, $durationMs)) {
            return $response;
        }

        $level      = $statusCode >= 500
            ? SystemLogLevel::ERROR
            : ($statusCode >= 400 ? SystemLogLevel::WARNING : SystemLogLevel::INFO);
        $route      = $request->route();
        $routeName  = $route?->getName();
        $userId     = Auth::id();
        $path       = $this->normalizePath($request);

        $this->systemLogManager->log(
            level: $level,
            category: 'http',
            message: sprintf('%s %s завершился со статусом %d.', $request->method(), $path, $statusCode),
            action: $routeName ?? $request->method() . ' ' . $request->path(),
            context: $durationMs >= $this->slowRequestThresholdMs()
                ? ['slow_request' => true]
                : null,
            routeName: is_string($routeName) ? $routeName : null,
            method: $request->method(),
            path: $path,
            statusCode: $statusCode,
            durationMs: $durationMs,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            userId: $this->resolveLoggableUserId($userId),
        );

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        return ! str($request->path())->startsWith(
            $this->ignoredPathPrefixes(),
        );
    }

    private function shouldPersist(Request $request, int $statusCode, int $durationMs): bool
    {
        if ($statusCode >= 500) {
            return (bool) config('system-logging.log_server_errors', true);
        }

        if ($statusCode >= 400) {
            return (bool) config('system-logging.log_client_errors', true);
        }

        if (! $request->isMethodSafe()) {
            return (bool) config('system-logging.log_unsafe_requests', true);
        }

        if (
            $durationMs >= $this->slowRequestThresholdMs()
            && $request->expectsJson()
        ) {
            return true;
        }

        return (bool) config('system-logging.log_safe_requests', false);
    }

    private function slowRequestThresholdMs(): int
    {
        $threshold = config('system-logging.slow_request_threshold_ms', 1500);

        return is_int($threshold) ? $threshold : 1500;
    }

    /**
     * @return array<int, string>
     */
    private function ignoredPathPrefixes(): array
    {
        $prefixes = config('system-logging.ignored_path_prefixes', []);

        if (! is_array($prefixes)) {
            return [];
        }

        return array_values(array_filter($prefixes, static fn(mixed $prefix): bool => is_string($prefix)));
    }

    private function normalizePath(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? '/' : '/' . $path;
    }

    private function resolveLoggableUserId(mixed $userId): ?string
    {
        if (! is_string($userId) && ! is_int($userId)) {
            return null;
        }

        $resolvedUserId = (string) $userId;

        return Str::isUuid($resolvedUserId)
            ? $resolvedUserId
            : null;
    }
}
