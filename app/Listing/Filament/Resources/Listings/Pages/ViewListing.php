<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Pages;

use App\Listing\Filament\Resources\Listings\ListingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewListing extends ViewRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
