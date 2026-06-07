<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\Pages\CreateCategory;
use App\Catalog\Filament\Resources\Categories\Pages\EditCategory;
use App\Catalog\Filament\Resources\Categories\Pages\ListCategories;
use App\Catalog\Filament\Resources\Categories\Pages\ViewCategory;
use App\Catalog\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Catalog\Filament\Resources\Categories\Schemas\CategoryInfolist;
use App\Catalog\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model                                   = EloquentCategory::class;

    protected static ?string $recordTitleAttribute                    = 'name';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedSquares2x2;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Squares2x2;

    protected static string | UnitEnum | null $navigationGroup        = 'Контент';

    protected static ?string $navigationLabel                         = 'Категории';

    protected static ?int $navigationSort                             = 10;

    public static function getModelLabel(): string
    {
        return __('Category');
    }

    public static function getPluralModelLabel(): string
    {
        return "Категории";
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) app(CategoryRepositoryInterface::class)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view'   => ViewCategory::route('/{record}'),
            'edit'   => EditCategory::route('/{record}/edit'),
        ];
    }
}
