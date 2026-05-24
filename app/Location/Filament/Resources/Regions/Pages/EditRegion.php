<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Pages;

use App\Location\Filament\Resources\Regions\RegionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRegion extends EditRecord
{
    protected static string $resource = RegionResource::class;

    public function getTitle(): string
    {
        return 'Редактировать регион';
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
