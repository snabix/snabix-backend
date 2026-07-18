<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Auth\CLI\CreateAdminCommand;
use App\Catalog\CLI\ImportCategoriesCommand;
use App\Location\CLI\ImportRussiaLocationsCommand;
use App\Media\CLI\CleanupOrphanFilesCommand;
use App\Shared\CLI\CleanupStorageCommand;
use App\Shared\CLI\CleanupSystemLogsCommand;
use App\Shared\CLI\RuntimeReadyCommand;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            CreateAdminCommand::class,
            ImportCategoriesCommand::class,
            ImportRussiaLocationsCommand::class,
            CleanupOrphanFilesCommand::class,
            CleanupStorageCommand::class,
            CleanupSystemLogsCommand::class,
            RuntimeReadyCommand::class,
        ]);
    }
}
