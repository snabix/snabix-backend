<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\HealthChecks;

use App\Shared\Infrastructure\HealthChecks\SystemResourcesHealthCheck;
use Tests\TestCase;
use UKFast\HealthCheck\Status;

class SystemResourcesHealthCheckTest extends TestCase
{
    public function test_system_resources_health_check_returns_disk_and_memory_metrics(): void
    {
        $check   = new SystemResourcesHealthCheck();

        $status  = $check->status();
        $context = $status->context();

        $this->assertContains($status->getStatus(), [
            Status::OKAY,
            Status::DEGRADED,
            Status::PROBLEM,
        ]);
        $this->assertArrayHasKey('disk_free_gb', $context);
        $this->assertArrayHasKey('disk_total_gb', $context);
        $this->assertArrayHasKey('disk_used_gb', $context);
        $this->assertArrayHasKey('disk_usage_percent', $context);
        $this->assertArrayHasKey('php_memory_usage_mb', $context);
        $this->assertArrayHasKey('php_peak_memory_mb', $context);
        $this->assertArrayHasKey('php_memory_limit_mb', $context);
        $this->assertArrayHasKey('php_memory_usage_percent', $context);
    }
}
