<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Pages;

use App\Listing\Filament\Resources\Listings\ListingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListListings extends ListRecords
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
