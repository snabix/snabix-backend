<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media;

use App\Media\Filament\Resources\Media\Pages\CreateMedia;
use App\Media\Filament\Resources\Media\Pages\EditMedia;
use App\Media\Filament\Resources\Media\Pages\ListMedia;
use App\Media\Filament\Resources\Media\Pages\ViewMedia;
use App\Media\Filament\Resources\Media\Schemas\MediaForm;
use App\Media\Filament\Resources\Media\Schemas\MediaInfolist;
use App\Media\Filament\Resources\Media\Tables\MediaTable;
use App\Media\Infrastructure\Models\EloquentMedia;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model                                   = EloquentMedia::class;

    protected static ?string $recordTitleAttribute                    = 'name';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedPhoto;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Photo;

    protected static string | UnitEnum | null $navigationGroup        = 'Контент';

    protected static ?string $navigationLabel                         = 'Медиафайлы';

    protected static ?int $navigationSort                             = 20;

    public static function getModelLabel(): string
    {
        return 'медиафайл';
    }

    public static function getPluralModelLabel(): string
    {
        return 'медиафайлы';
    }

    public static function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MediaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentMedia::query()->count();
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
            'index'  => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'view'   => ViewMedia::route('/{record}'),
            'edit'   => EditMedia::route('/{record}/edit'),
        ];
    }
}
