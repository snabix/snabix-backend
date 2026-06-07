<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Pages;

use App\Media\Filament\Resources\Media\MediaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMedia extends ViewRecord
{
    protected static string $resource = MediaResource::class;

    protected static ?string $title   = 'Просмотр медиафайла';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }
}
