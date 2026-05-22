<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Pages;

use App\Location\Filament\Resources\Regions\RegionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegion extends CreateRecord
{
    protected static string $resource = RegionResource::class;

    public function getTitle(): string
    {
        return 'Создать регион';
    }

    protected function getRedirectUrl(): string
    {
        return RegionResource::getUrl('index');
    }
}
