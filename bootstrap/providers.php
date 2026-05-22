<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Providers\AuthServiceProvider;
use App\Catalog\Infrastructure\Providers\CatalogServiceProvider;
use App\Listing\Infrastructure\Providers\ListingServiceProvider;
use App\Location\Infrastructure\Providers\LocationServiceProvider;
use App\Shared\Infrastructure\Providers\AppServiceProvider;
use App\Shared\Infrastructure\Providers\ConsoleServiceProvider;
use App\Shared\Infrastructure\Providers\EventServiceProvider;
use App\Shared\Infrastructure\Providers\Filament\AdminPanelProvider;

return [
    AdminPanelProvider::class,
    AuthServiceProvider::class,
    CatalogServiceProvider::class,
    ListingServiceProvider::class,
    LocationServiceProvider::class,
    AppServiceProvider::class,
    ConsoleServiceProvider::class,
    EventServiceProvider::class,
];
