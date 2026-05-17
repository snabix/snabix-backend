<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

use App\Shared\Domain\Enums\SystemLogLevel;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemLogManager
{
    /**
     * @param array<string, mixed>|null $context
     */
    public function log(
        SystemLogLevel $level,
        string $category,
        string $message,
        ?string $action = null,
        ?array $context = null,
        ?string $routeName = null,
        ?string $method = null,
        ?string $path = null,
        ?int $statusCode = null,
        ?int $durationMs = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $userId = null,
    ): void {
        try {
            EloquentSystemLog::query()->create([
                'level'       => $level,
                'category'    => $category,
                'action'      => $action,
                'message'     => $message,
                'context'     => $context,
                'route_name'  => $routeName,
                'method'      => $method,
                'path'        => $path,
                'status_code' => $statusCode,
                'duration_ms' => $durationMs,
                'ip_address'  => $ipAddress,
                'user_agent'  => $userAgent,
                'user_id'     => $userId,
            ]);
        } catch (Throwable $exception) {
            Log::channel('stderr')->error('System log persistence failed.', [
                'category'         => $category,
                'message'          => $message,
                'action'           => $action,
                'system_log_error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function info(
        string $category,
        string $message,
        ?string $action = null,
        ?array $context = null,
        ?string $userId = null,
    ): void {
        $this->log(SystemLogLevel::INFO, $category, $message, $action, $context, userId: $userId);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function error(
        string $category,
        string $message,
        ?string $action = null,
        ?array $context = null,
        ?string $userId = null,
    ): void {
        $this->log(SystemLogLevel::ERROR, $category, $message, $action, $context, userId: $userId);
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function warning(
        string $category,
        string $message,
        ?string $action = null,
        ?array $context = null,
        ?string $userId = null,
    ): void {
        $this->log(SystemLogLevel::WARNING, $category, $message, $action, $context, userId: $userId);
    }
}
