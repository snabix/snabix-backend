<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Pages;

use App\Listing\Filament\Resources\Listings\ListingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditListing extends EditRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
