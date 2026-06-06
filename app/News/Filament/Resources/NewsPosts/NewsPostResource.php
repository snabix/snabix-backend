<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts;

use App\News\Filament\Resources\NewsPosts\Pages\CreateNewsPost;
use App\News\Filament\Resources\NewsPosts\Pages\EditNewsPost;
use App\News\Filament\Resources\NewsPosts\Pages\ListNewsPosts;
use App\News\Filament\Resources\NewsPosts\Pages\ViewNewsPost;
use App\News\Filament\Resources\NewsPosts\Schemas\NewsPostForm;
use App\News\Filament\Resources\NewsPosts\Schemas\NewsPostInfolist;
use App\News\Filament\Resources\NewsPosts\Tables\NewsPostsTable;
use App\News\Infrastructure\Models\EloquentNewsPost;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NewsPostResource extends Resource
{
    protected static ?string $model                                   = EloquentNewsPost::class;

    protected static ?string $recordTitleAttribute                    = 'title';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedNewspaper;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Newspaper;

    protected static string | UnitEnum | null $navigationGroup        = 'Контент';

    protected static ?string $navigationLabel                         = 'Новости';

    protected static ?int $navigationSort                             = 10;

    public static function getModelLabel(): string
    {
        return 'новость';
    }

    public static function getPluralModelLabel(): string
    {
        return 'новости';
    }

    public static function form(Schema $schema): Schema
    {
        return NewsPostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NewsPostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsPostsTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentNewsPost::query()->count();
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListNewsPosts::route('/'),
            'create' => CreateNewsPost::route('/create'),
            'view'   => ViewNewsPost::route('/{record}'),
            'edit'   => EditNewsPost::route('/{record}/edit'),
        ];
    }
}
