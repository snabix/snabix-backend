<?php

declare(strict_types=1);

namespace App\Shared\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use UKFast\HealthCheck\Facade\HealthCheck;
use UKFast\HealthCheck\HealthCheck as HealthCheckContract;

class SystemHealthOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $statuses = $this->resolveStatuses();
        $checksCount = $statuses->count();
        $problemCount = $statuses->where('status', 'PROBLEM')->count();
        $degradedCount = $statuses->where('status', 'DEGRADED')->count();
        $okayCount = $statuses->where('status', 'OK')->count();
        $systemCheck = $statuses->firstWhere('name', 'system_resources');
        $systemCheck = is_array($systemCheck) ? $systemCheck : null;

        return [
            Stat::make('Проверки системы', sprintf('%d из %d', $okayCount, $checksCount))
                ->description('Ошибок: ' . $problemCount . ' • Предупреждений: ' . $degradedCount)
                ->color($problemCount > 0 ? 'danger' : ($degradedCount > 0 ? 'warning' : 'success')),
            Stat::make('Диск', $this->contextString($systemCheck, 'disk_free_gb', '0') . ' ГБ свободно')
                ->description(
                    'Использовано: ' . $this->contextString($systemCheck, 'disk_usage_percent', '0') . '%'
                        . ' • Всего: ' . $this->contextString($systemCheck, 'disk_total_gb', '0') . ' ГБ',
                )
                ->color($this->resolveUsageColor($this->contextInt($systemCheck, 'disk_usage_percent'))),
            Stat::make('Память PHP', $this->contextString($systemCheck, 'php_memory_usage_mb', '0') . ' МБ')
                ->description(
                    'Пик: ' . $this->contextString($systemCheck, 'php_peak_memory_mb', '0') . ' МБ'
                        . ' • Лимит: ' . $this->contextString($systemCheck, 'php_memory_limit_mb', '0') . ' МБ',
                )
                ->color($this->resolveUsageColor($this->contextInt($systemCheck, 'php_memory_usage_percent'))),
        ];
    }

    protected function getHeading(): string
    {
        return 'Обзор состояния системы';
    }

    /**
     * @return Collection<int, array{name: string, status: string|null, message: string, context: array<string, mixed>}>
     */
    private function resolveStatuses(): Collection
    {
        /** @var Collection<int, HealthCheckContract> $checks */
        $checks = HealthCheck::all();

        return $checks->map(function (HealthCheckContract $check): array {
            $status = $check->status();

            return [
                'name' => $check->name(),
                'status' => $status->getStatus(),
                'message' => $status->message(),
                'context' => $status->context(),
            ];
        });
    }

    /**
     * @param array{name: string, status: string|null, message: string, context: array<string, mixed>}|null $status
     */
    private function contextString(?array $status, string $key, string $fallback): string
    {
        $value = $status['context'][$key] ?? null;

        return is_string($value) || is_int($value) || is_float($value)
            ? (string) $value
            : $fallback;
    }

    /**
     * @param array{name: string, status: string|null, message: string, context: array<string, mixed>}|null $status
     */
    private function contextInt(?array $status, string $key): int
    {
        $value = $status['context'][$key] ?? null;

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }

    private function resolveUsageColor(int $usagePercent): string
    {
        return match (true) {
            $usagePercent >= 90 => 'danger',
            $usagePercent >= 75 => 'warning',
            default => 'success',
        };
    }
}
