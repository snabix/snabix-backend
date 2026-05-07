<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\ErrorLogs\Pages;

use App\Shared\Filament\Resources\ErrorLogs\ErrorLogResource;
use Filament\Resources\Pages\ListRecords;

class ListErrorLogs extends ListRecords
{
    protected static string $resource = ErrorLogResource::class;

    public function getTitle(): string
    {
        return 'Ошибки и предупреждения';
    }
}
