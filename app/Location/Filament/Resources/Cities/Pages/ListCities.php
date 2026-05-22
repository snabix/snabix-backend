<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Pages;

use App\Location\Filament\Resources\Cities\CityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListCities extends ListRecords
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return 'Города';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Создать')
                ->icon(Heroicon::OutlinedPlus),
        ];
    }
}
