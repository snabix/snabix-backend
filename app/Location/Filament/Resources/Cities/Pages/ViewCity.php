<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Pages;

use App\Location\Filament\Resources\Cities\CityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCity extends ViewRecord
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return 'Просмотр города';
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
