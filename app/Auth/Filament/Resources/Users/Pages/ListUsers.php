<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Pages;

use App\Auth\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Новый пользователь')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
