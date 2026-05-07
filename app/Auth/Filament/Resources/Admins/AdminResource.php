<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins;

use App\Auth\Filament\Resources\Admins\Pages\CreateAdmin;
use App\Auth\Filament\Resources\Admins\Pages\EditAdmin;
use App\Auth\Filament\Resources\Admins\Pages\ListAdmins;
use App\Auth\Filament\Resources\Admins\Pages\ViewAdmin;
use App\Auth\Filament\Resources\Admins\Schemas\AdminForm;
use App\Auth\Filament\Resources\Admins\Schemas\AdminInfolist;
use App\Auth\Filament\Resources\Admins\Tables\AdminsTable;
use App\Auth\Infrastructure\Models\EloquentAdmin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdminResource extends Resource
{
    protected static ?string $model = EloquentAdmin::class;

    protected static ?string $modelLabel = 'администратор';

    protected static ?string $pluralModelLabel = 'администраторы';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ShieldCheck;

    protected static string | UnitEnum | null $navigationGroup = 'Управление доступом';

    protected static ?string $navigationLabel = 'Администраторы';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentAdmin::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'view' => ViewAdmin::route('/{record}'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }
}
