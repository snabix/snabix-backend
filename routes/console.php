<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('shared:cleanup-system-logs')
    ->dailyAt('03:30')
    ->withoutOverlapping();

$failedJobsRetentionHours = config('queue.maintenance.failed_retention_hours', 168);

if (! is_int($failedJobsRetentionHours)) {
    $failedJobsRetentionHours = 168;
}

Schedule::command(sprintf(
    'queue:prune-failed --hours=%d',
    $failedJobsRetentionHours,
))
    ->dailyAt('03:45')
    ->withoutOverlapping();

Schedule::command('auth:clear-resets')
    ->dailyAt('04:00')
    ->withoutOverlapping();
