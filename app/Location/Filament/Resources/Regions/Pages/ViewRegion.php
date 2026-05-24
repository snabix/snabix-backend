<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Pages;

use App\Location\Filament\Resources\Regions\RegionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRegion extends ViewRecord
{
    protected static string $resource = RegionResource::class;

    public function getTitle(): string
    {
        return 'Просмотр региона';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->translateLabel(),
            DeleteAction::make()
                ->translateLabel(),
        ];
    }
}
