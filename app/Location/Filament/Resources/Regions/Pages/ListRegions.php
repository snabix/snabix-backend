<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Pages;

use App\Location\Filament\Resources\Regions\RegionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListRegions extends ListRecords
{
    protected static string $resource = RegionResource::class;

    public function getTitle(): string
    {
        return 'Регионы';
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
