<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings;

use App\Listing\Filament\Resources\Listings\Pages\CreateListing;
use App\Listing\Filament\Resources\Listings\Pages\EditListing;
use App\Listing\Filament\Resources\Listings\Pages\ListListings;
use App\Listing\Filament\Resources\Listings\Pages\ViewListing;
use App\Listing\Filament\Resources\Listings\Schemas\ListingForm;
use App\Listing\Filament\Resources\Listings\Schemas\ListingInfolist;
use App\Listing\Filament\Resources\Listings\Tables\ListingsTable;
use App\Listing\Infrastructure\Models\EloquentListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ListingResource extends Resource
{
    protected static ?string $model                                   = EloquentListing::class;

    protected static ?string $recordTitleAttribute                    = 'title';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedNewspaper;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Newspaper;

    protected static string | UnitEnum | null $navigationGroup        = 'Контент';

    protected static ?string $navigationLabel                         = 'Объявления';

    protected static ?int $navigationSort                             = 12;

    public static function getModelLabel(): string
    {
        return 'объявление';
    }

    public static function getPluralModelLabel(): string
    {
        return 'объявления';
    }

    public static function form(Schema $schema): Schema
    {
        return ListingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ListingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListListings::route('/'),
            'create' => CreateListing::route('/create'),
            'view'   => ViewListing::route('/{record}'),
            'edit'   => EditListing::route('/{record}/edit'),
        ];
    }
}
