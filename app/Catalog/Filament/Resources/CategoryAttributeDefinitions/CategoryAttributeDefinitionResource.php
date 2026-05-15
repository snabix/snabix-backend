<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions;

use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages\CreateCategoryAttributeDefinition;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages\EditCategoryAttributeDefinition;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages\ListCategoryAttributeDefinitions;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Pages\ViewCategoryAttributeDefinition;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Schemas\CategoryAttributeDefinitionForm;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Schemas\CategoryAttributeDefinitionInfolist;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Tables\CategoryAttributeDefinitionsTable;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoryAttributeDefinitionResource extends Resource
{
    protected static ?string $model                                   = EloquentCategoryAttributeDefinition::class;

    protected static ?string $recordTitleAttribute                    = 'name';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string | UnitEnum | null $navigationGroup        = 'Контент';

    protected static ?string $navigationLabel                         = 'Характеристики';

    protected static ?int $navigationSort                             = 11;

    public static function getModelLabel(): string
    {
        return __('Category attribute');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Category attributes');
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryAttributeDefinitionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryAttributeDefinitionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryAttributeDefinitionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCategoryAttributeDefinitions::route('/'),
            'create' => CreateCategoryAttributeDefinition::route('/create'),
            'view'   => ViewCategoryAttributeDefinition::route('/{record}'),
            'edit'   => EditCategoryAttributeDefinition::route('/{record}/edit'),
        ];
    }
}
