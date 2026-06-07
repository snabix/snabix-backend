<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Pages;

use App\Location\Filament\Resources\Cities\CityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCity extends EditRecord
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return 'Редактировать город';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->translateLabel(),
            DeleteAction::make()
                ->translateLabel(),
        ];
    }
}
