<?php

declare(strict_types=1);

namespace App\Shared\CLI;

use App\Shared\Infrastructure\HealthChecks\RabbitMqHealthCheck;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;
use UKFast\HealthCheck\Checks\DatabaseHealthCheck;
use UKFast\HealthCheck\Checks\MigrationUpToDateHealthCheck;
use UKFast\HealthCheck\Checks\RedisHealthCheck;
use UKFast\HealthCheck\Checks\SchedulerHealthCheck;
use UKFast\HealthCheck\HealthCheck;

#[AsCommand(name: 'runtime:ready')]
class RuntimeReadyCommand extends Command
{
    /** @var array<string, list<class-string<HealthCheck>>> */
    private const array COMPONENT_CHECKS = [
        'app'       => [
            DatabaseHealthCheck::class,
            MigrationUpToDateHealthCheck::class,
            RedisHealthCheck::class,
            RabbitMqHealthCheck::class,
        ],
        'worker'    => [
            DatabaseHealthCheck::class,
            MigrationUpToDateHealthCheck::class,
            RedisHealthCheck::class,
            RabbitMqHealthCheck::class,
        ],
        'scheduler' => [
            DatabaseHealthCheck::class,
            MigrationUpToDateHealthCheck::class,
            RedisHealthCheck::class,
            RabbitMqHealthCheck::class,
            SchedulerHealthCheck::class,
        ],
    ];

    /** @var array<string, string> */
    private const array PROCESS_MARKERS  = [
        'app'       => 'php-fpm',
        'worker'    => 'queue:work',
        'scheduler' => 'schedule:work',
    ];

    protected $signature                 = 'runtime:ready
        {component : Runtime component: app, worker or scheduler}';

    protected $description               = 'Проверить готовность production-компонента';

    public function handle(): int
    {
        $component = $this->argument('component');

        if (! array_key_exists($component, self::COMPONENT_CHECKS)) {
            $this->components->error('Unknown runtime component. Expected app, worker or scheduler.');

            return self::INVALID;
        }

        if (! $this->processMatches(self::PROCESS_MARKERS[$component])) {
            $this->components->error(sprintf('The %s process is not running as PID 1.', $component));

            return self::FAILURE;
        }

        foreach (self::COMPONENT_CHECKS[$component] as $checkClass) {
            try {
                $check  = app($checkClass);
                $status = $check->status();
            } catch (Throwable $exception) {
                $this->components->error(sprintf(
                    '%s readiness check crashed: %s',
                    $checkClass,
                    $exception->getMessage(),
                ));

                return self::FAILURE;
            }

            if ($status->isProblem()) {
                $this->components->error(sprintf(
                    '%s readiness check failed: %s',
                    $check->name(),
                    $status->message(),
                ));

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function processMatches(string $marker): bool
    {
        $command = @file_get_contents('/proc/1/cmdline');

        return is_string($command) && str_contains($command, $marker);
    }
}
