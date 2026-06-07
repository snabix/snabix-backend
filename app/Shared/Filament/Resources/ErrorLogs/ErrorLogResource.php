<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\ErrorLogs;

use App\Shared\Domain\Enums\SystemLogLevel;
use App\Shared\Filament\Resources\ErrorLogs\Pages\ListErrorLogs;
use App\Shared\Filament\Resources\ErrorLogs\Pages\ViewErrorLog;
use App\Shared\Filament\Resources\SystemLogs\Schemas\SystemLogInfolist;
use App\Shared\Filament\Resources\SystemLogs\Tables\SystemLogsTable;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ErrorLogResource extends Resource
{
    protected static ?string $model                                   = EloquentSystemLog::class;

    protected static ?string $recordTitleAttribute                    = 'message';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedExclamationTriangle;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ExclamationTriangle;

    protected static ?int $navigationSort                             = 71;

    public static function getModelLabel(): string
    {
        return 'ошибка системы';
    }

    public static function getPluralModelLabel(): string
    {
        return 'ошибки системы';
    }

    public static function getNavigationGroup(): string
    {
        return 'Система';
    }

    public static function getNavigationLabel(): string
    {
        return 'Ошибки';
    }

    public static function table(Table $table): Table
    {
        return SystemLogsTable::configure($table, onlyErrors: true);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SystemLogInfolist::configure($schema);
    }

    public static function getNavigationBadge(): string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Builder<Model> $query */
        $query = parent::getEloquentQuery();

        $query->whereIn('level', [
            SystemLogLevel::WARNING->value,
            SystemLogLevel::ERROR->value,
            SystemLogLevel::CRITICAL->value,
        ]);

        return $query;
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListErrorLogs::route('/'),
            'view'  => ViewErrorLog::route('/{record}'),
        ];
    }
}
