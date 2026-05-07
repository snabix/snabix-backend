<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs\Pages;

use App\Shared\Filament\Resources\SystemLogs\SystemLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSystemLogs extends ListRecords
{
    protected static string $resource = SystemLogResource::class;

    public function getTitle(): string
    {
        return 'Журнал действий системы';
    }
}
