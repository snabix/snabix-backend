<?php

declare(strict_types=1);

namespace App\CLI;

use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'shared:cleanup-system-logs')]
class SharedCLICleanupSystemLogs extends Command
{
    protected $signature   = 'shared:cleanup-system-logs
        {--days= : За сколько дней хранить системные логи}';

    protected $description = 'Удалить устаревшие записи системных логов';

    public function handle(): int
    {
        $days = $this->resolveRetentionDays();

        $cutoff = CarbonImmutable::now()->subDays($days);

        $deleted = EloquentSystemLog::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $deletedCount = is_int($deleted) ? $deleted : 0;

        $this->components->info(sprintf(
            'Удалено %d записей системных логов старше %d дней.',
            $deletedCount,
            $days,
        ));

        return self::SUCCESS;
    }

    private function resolveRetentionDays(): int
    {
        $days = $this->option('days');

        if (is_numeric($days) && (int) $days > 0) {
            return (int) $days;
        }

        $configuredDays = config('system-logging.retention_days', 30);

        if (is_int($configuredDays) && $configuredDays > 0) {
            return $configuredDays;
        }

        return 30;
    }
}
