<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use App\Auth\CLI\CreateAdminCommand;
use App\Catalog\CLI\ImportCategoriesCommand;
use App\Development\CLI\BootstrapDemoDataCommand;
use App\Location\CLI\ImportRussiaLocationsCommand;
use App\Media\CLI\CleanupOrphanFilesCommand;
use App\Shared\CLI\CleanupStorageCommand;
use App\Shared\CLI\CleanupSystemLogsCommand;
use App\Shared\CLI\RuntimeReadyCommand;
use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

class ConsoleCommandRegistrationTest extends TestCase
{
    public function test_project_commands_keep_their_public_artisan_signatures(): void
    {
        $commands         = $this->app->make(Kernel::class)->all();

        $expectedCommands = [
            'app:bootstrap-demo-data'     => BootstrapDemoDataCommand::class,
            'app:make-admin'              => CreateAdminCommand::class,
            'auth:make-admin'             => CreateAdminCommand::class,
            'catalog:import-categories'   => ImportCategoriesCommand::class,
            'location:import-russia'      => ImportRussiaLocationsCommand::class,
            'media:cleanup-orphans'       => CleanupOrphanFilesCommand::class,
            'shared:cleanup-storage'      => CleanupStorageCommand::class,
            'shared:cleanup-system-logs'  => CleanupSystemLogsCommand::class,
            'runtime:ready'               => RuntimeReadyCommand::class,
        ];

        foreach ($expectedCommands as $signature => $commandClass) {
            $this->assertArrayHasKey($signature, $commands);
            $this->assertInstanceOf($commandClass, $commands[$signature]);
        }
    }
}
