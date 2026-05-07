<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Middleware;

use App\Shared\Domain\Enums\SystemLogLevel;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

readonly class LogRequestActivity
{
    public function __construct(
        private SystemLogManager $systemLogManager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        /** @var Response $response */
        $response = $next($request);

        if (! $this->shouldLog($request)) {
            return $response;
        }

        $statusCode = $response->getStatusCode();
        $level = $statusCode >= 500
            ? SystemLogLevel::ERROR
            : ($statusCode >= 400 ? SystemLogLevel::WARNING : SystemLogLevel::INFO);
        $routeName = $request->route()->getName();
        $userId = Auth::id();
        $path = $this->normalizePath($request);

        $this->systemLogManager->log(
            level: $level,
            category: 'http',
            message: sprintf('%s %s завершился со статусом %d.', $request->method(), $path, $statusCode),
            action: $routeName ?? $request->method() . ' ' . $request->path(),
            context: [
                'query' => $request->query(),
            ],
            routeName: is_string($routeName) ? $routeName : null,
            method: $request->method(),
            path: $path,
            statusCode: $statusCode,
            durationMs: (int) round((microtime(true) - $startedAt) * 1000),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            userId: is_string($userId) || is_int($userId) ? (string) $userId : null,
        );

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if ($request->expectsJson() && $request->isMethodSafe() && $request->query() === []) {
            return ! str($request->path())->startsWith([
                '_ignition',
                'livewire',
                'up',
                'health',
                'ping',
                'docs',
                'api/documentation',
                'storage',
            ]);
        }

        return ! str($request->path())->startsWith([
            '_ignition',
            'livewire',
            'up',
            'health',
            'ping',
            'docs',
            'api/documentation',
            'storage',
        ]);
    }

    private function normalizePath(Request $request): string
    {
        $path = trim($request->path(), '/');

        return $path === '' ? '/' : '/' . $path;
    }
}
