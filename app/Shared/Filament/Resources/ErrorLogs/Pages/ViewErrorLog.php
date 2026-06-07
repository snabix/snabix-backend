<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\ErrorLogs\Pages;

use App\Shared\Filament\Resources\ErrorLogs\ErrorLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewErrorLog extends ViewRecord
{
    protected static string $resource = ErrorLogResource::class;

    public function getTitle(): string
    {
        return 'Просмотр ошибки';
    }
}
