<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Pages;

use App\Listing\Filament\Resources\Listings\ListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    protected static string $resource = ListingResource::class;
}
