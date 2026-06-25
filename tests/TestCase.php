<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $database = getenv('DB_DATABASE') ?: null;

        if ((getenv('APP_ENV') ?: null) === 'testing' && $database !== 'snabix_test') {
            self::fail(sprintf(
                'Tests must use the isolated database [snabix_test], current database is [%s].',
                $database ?? 'null',
            ));
        }

        parent::setUp();
    }

    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $this->ensureSafeTestDatabaseConfiguration($app);

        return $app;
    }

    final protected function ensureSafeTestDatabaseConfiguration(?Application $app = null): void
    {
        $application = $app ?? $this->app;
        $connection  = $application->make('config')->get('database.default');

        if (! is_string($connection)) {
            throw new RuntimeException(sprintf(
                'Unsafe test database connection type [%s].',
                get_debug_type($connection),
            ));
        }

        $host        = $application->make('config')->get("database.connections.{$connection}.host");
        $database    = $application->make('config')->get("database.connections.{$connection}.database");

        if (
            ! $application->environment('testing')
            || $connection !== 'pgsql'
            || $host !== 'db-test'
            || $database !== 'snabix_test'
        ) {
            throw new RuntimeException(sprintf(
                'Unsafe test database configuration: env [%s], connection [%s], host [%s], database [%s].',
                $application->environment(),
                $connection,
                is_scalar($host) ? (string) $host : get_debug_type($host),
                is_scalar($database) ? (string) $database : get_debug_type($database),
            ));
        }
    }
}
