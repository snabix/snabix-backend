<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Pages;

use App\Auth\Filament\Resources\Admins\AdminResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

    protected static ?string $title   = 'Просмотр администратора';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
        ];
    }
}
