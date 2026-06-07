<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Providers;

use App\Location\Application\Services\RussiaLocationImporter;
use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RussiaLocationImporter::class);
    }
}
