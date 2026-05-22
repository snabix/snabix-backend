<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Pages;

use App\Location\Filament\Resources\Cities\CityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCity extends CreateRecord
{
    protected static string $resource = CityResource::class;

    public function getTitle(): string
    {
        return 'Создать город';
    }

    protected function getRedirectUrl(): string
    {
        return CityResource::getUrl('index');
    }
}
