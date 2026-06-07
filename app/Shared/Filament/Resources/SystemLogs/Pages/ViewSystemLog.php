<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs\Pages;

use App\Shared\Filament\Resources\SystemLogs\SystemLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSystemLog extends ViewRecord
{
    protected static string $resource = SystemLogResource::class;

    public function getTitle(): string
    {
        return 'Просмотр записи журнала';
    }
}
