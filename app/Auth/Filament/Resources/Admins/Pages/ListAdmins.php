<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Pages;

use App\Auth\Filament\Resources\Admins\AdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Новый администратор')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
