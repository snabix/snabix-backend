<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities;

use App\Location\Filament\Resources\Cities\Pages\CreateCity;
use App\Location\Filament\Resources\Cities\Pages\EditCity;
use App\Location\Filament\Resources\Cities\Pages\ListCities;
use App\Location\Filament\Resources\Cities\Pages\ViewCity;
use App\Location\Filament\Resources\Cities\Schemas\CityForm;
use App\Location\Filament\Resources\Cities\Schemas\CityInfolist;
use App\Location\Filament\Resources\Cities\Tables\CitiesTable;
use App\Location\Infrastructure\Models\EloquentCity;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CityResource extends Resource
{
    protected static ?string $model                                   = EloquentCity::class;

    protected static ?string $recordTitleAttribute                    = 'name';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedMap;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Map;

    protected static string | UnitEnum | null $navigationGroup        = 'Локации';

    protected static ?string $navigationLabel                         = 'Города';

    protected static ?int $navigationSort                             = 20;

    public static function getModelLabel(): string
    {
        return 'город';
    }

    public static function getPluralModelLabel(): string
    {
        return 'города';
    }

    public static function form(Schema $schema): Schema
    {
        return CityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitiesTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentCity::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'view'   => ViewCity::route('/{record}'),
            'edit'   => EditCity::route('/{record}/edit'),
        ];
    }
}
