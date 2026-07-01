<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\CLI\AuthCLIMakeAdminUser;
use App\CLI\CatalogCLIImportCategories;
use App\CLI\LocationCLIImportRussiaLocations;
use App\CLI\MediaCLICleanupOrphanFiles;
use App\CLI\SharedCLICleanupStorage;
use App\CLI\SharedCLICleanupSystemLogs;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            AuthCLIMakeAdminUser::class,
            CatalogCLIImportCategories::class,
            LocationCLIImportRussiaLocations::class,
            MediaCLICleanupOrphanFiles::class,
            SharedCLICleanupStorage::class,
            SharedCLICleanupSystemLogs::class,
        ]);
    }
}
