<?php

declare(strict_types=1);

use App\Shared\Infrastructure\Providers\AppServiceProvider;
use App\Shared\Infrastructure\Providers\ConsoleServiceProvider;
use App\Shared\Infrastructure\Providers\EventServiceProvider;
use App\Shared\Infrastructure\Providers\Filament\AdminPanelProvider;

return [
    AdminPanelProvider::class,
    AppServiceProvider::class,
    ConsoleServiceProvider::class,
    EventServiceProvider::class,
];
