<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;

class SchedulerHealthCheckTest extends TestCase
{
    public function test_scheduler_heartbeat_changes_readiness_to_healthy(): void
    {
        $cacheKey = config('healthcheck.scheduler.cache-key');
        $this->assertIsString($cacheKey);

        Cache::forget($cacheKey);

        $this->assertTrue(app(SchedulerHealthCheck::class)->status()->isProblem());

        Artisan::call('health-check:cache-scheduler-running');

        $this->assertTrue(app(SchedulerHealthCheck::class)->status()->isOkay());
    }
}
