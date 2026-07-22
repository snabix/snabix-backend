<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users;

use App\Auth\Filament\Resources\Users\Pages\CreateUser;
use App\Auth\Filament\Resources\Users\Pages\EditUser;
use App\Auth\Filament\Resources\Users\Pages\ListUsers;
use App\Auth\Filament\Resources\Users\Pages\ViewUser;
use App\Auth\Filament\Resources\Users\Schemas\UserForm;
use App\Auth\Filament\Resources\Users\Schemas\UserInfolist;
use App\Auth\Filament\Resources\Users\Tables\UsersTable;
use App\Auth\Infrastructure\Models\EloquentUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model                                   = EloquentUser::class;

    protected static ?string $modelLabel                              = 'пользователь';

    protected static ?string $pluralModelLabel                        = 'пользователи';

    protected static ?string $recordTitleAttribute                    = 'account_label';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedUsers;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Users;

    protected static string | UnitEnum | null $navigationGroup        = 'Управление доступом';

    protected static ?string $navigationLabel                         = 'Пользователи';

    protected static ?int $navigationSort                             = 10;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentUser::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'primary';
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view'   => ViewUser::route('/{record}'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
