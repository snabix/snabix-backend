<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Pages;

use App\Media\Filament\Resources\Media\MediaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected static ?string $title   = 'Медиафайлы';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Загрузить файл'),
        ];
    }
}
