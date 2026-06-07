<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthChecks;

use UKFast\HealthCheck\HealthCheck;
use UKFast\HealthCheck\Status;

class SystemResourcesHealthCheck extends HealthCheck
{
    protected string $name = 'system_resources';

    public function status(): Status
    {
        $configuredDisk     = config('media-library.disk_name', 'public');
        $disk               = is_string($configuredDisk) ? $configuredDisk : 'public';
        $configuredRoot     = config("filesystems.disks.{$disk}.root", storage_path('app/public'));
        $root               = is_string($configuredRoot) ? $configuredRoot : storage_path('app/public');
        $freeBytesValue     = @disk_free_space($root);
        $totalBytesValue    = @disk_total_space($root);
        $freeBytes          = is_numeric($freeBytesValue) ? (int) $freeBytesValue : null;
        $totalBytes         = is_numeric($totalBytesValue) ? (int) $totalBytesValue : null;
        $usedBytes          = ($freeBytes !== null && $totalBytes !== null) ? max($totalBytes - $freeBytes, 0) : null;
        $memoryLimitBytes   = $this->toBytes(ini_get('memory_limit'));
        $memoryUsageBytes   = memory_get_usage(true);
        $peakMemoryBytes    = memory_get_peak_usage(true);
        $memoryUsagePercent = $this->percentage($memoryUsageBytes, $memoryLimitBytes);
        $diskUsagePercent   = $this->percentage($usedBytes, $totalBytes);

        $context            = [
            'media_disk'               => $disk,
            'media_root'               => $root,
            'disk_free_gb'             => $this->formatGb($freeBytes),
            'disk_total_gb'            => $this->formatGb($totalBytes),
            'disk_used_gb'             => $this->formatGb($usedBytes),
            'disk_usage_percent'       => $diskUsagePercent,
            'php_memory_usage_mb'      => $this->formatMb($memoryUsageBytes),
            'php_peak_memory_mb'       => $this->formatMb($peakMemoryBytes),
            'php_memory_limit_mb'      => $this->formatMb($memoryLimitBytes),
            'php_memory_usage_percent' => $memoryUsagePercent,
        ];

        if ($freeBytes !== null && $freeBytes < 1024 * 1024 * 1024) {
            return $this->degraded('Low free disk space on media storage.', $context);
        }

        if (is_int($memoryLimitBytes) && $memoryLimitBytes > 0 && $memoryUsageBytes >= (int) floor($memoryLimitBytes * 0.9)) {
            return $this->degraded('PHP memory usage is close to the configured limit.', $context);
        }

        return $this->okay($context);
    }

    private function toBytes(string | false $value): ?int
    {
        if ($value === false || $value === '' || $value === '-1') {
            return null;
        }

        $trimmed = strtolower(trim($value));
        $number  = (float) $trimmed;
        $suffix  = substr($trimmed, -1);

        return match ($suffix) {
            'g'     => (int) ($number * 1024 * 1024 * 1024),
            'm'     => (int) ($number * 1024 * 1024),
            'k'     => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    private function formatGb(?int $bytes): int
    {
        if ($bytes === null) {
            return 0;
        }

        return (int) floor($bytes / 1024 / 1024 / 1024);
    }

    private function formatMb(?int $bytes): int
    {
        if ($bytes === null) {
            return 0;
        }

        return (int) floor($bytes / 1024 / 1024);
    }

    private function percentage(?int $used, ?int $total): int
    {
        if ($used === null || $total === null || $total <= 0) {
            return 0;
        }

        return (int) floor(($used / $total) * 100);
    }
}
