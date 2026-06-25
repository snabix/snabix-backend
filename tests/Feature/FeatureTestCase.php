<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use LazilyRefreshDatabase;

    protected bool $seed     = true;

    protected string $seeder = TestDatabaseSeeder::class;

    protected function beforeRefreshingDatabase(): void
    {
        $this->ensureSafeTestDatabaseConfiguration();
    }
}
