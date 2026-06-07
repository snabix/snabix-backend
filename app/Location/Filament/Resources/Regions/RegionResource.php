<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions;

use App\Location\Filament\Resources\Regions\Pages\CreateRegion;
use App\Location\Filament\Resources\Regions\Pages\EditRegion;
use App\Location\Filament\Resources\Regions\Pages\ListRegions;
use App\Location\Filament\Resources\Regions\Pages\ViewRegion;
use App\Location\Filament\Resources\Regions\Schemas\RegionForm;
use App\Location\Filament\Resources\Regions\Schemas\RegionInfolist;
use App\Location\Filament\Resources\Regions\Tables\RegionsTable;
use App\Location\Infrastructure\Models\EloquentRegion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RegionResource extends Resource
{
    protected static ?string $model                                   = EloquentRegion::class;

    protected static ?string $recordTitleAttribute                    = 'name';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedMapPin;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::MapPin;

    protected static string | UnitEnum | null $navigationGroup        = 'Локации';

    protected static ?string $navigationLabel                         = 'Регионы';

    protected static ?int $navigationSort                             = 10;

    public static function getModelLabel(): string
    {
        return 'регион';
    }

    public static function getPluralModelLabel(): string
    {
        return 'регионы';
    }

    public static function form(Schema $schema): Schema
    {
        return RegionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RegionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegionsTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentRegion::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }

    /**
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListRegions::route('/'),
            'create' => CreateRegion::route('/create'),
            'view'   => ViewRegion::route('/{record}'),
            'edit'   => EditRegion::route('/{record}/edit'),
        ];
    }
}
