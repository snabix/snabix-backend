<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs;

use App\Shared\Filament\Resources\SystemLogs\Pages\ListSystemLogs;
use App\Shared\Filament\Resources\SystemLogs\Pages\ViewSystemLog;
use App\Shared\Filament\Resources\SystemLogs\Schemas\SystemLogInfolist;
use App\Shared\Filament\Resources\SystemLogs\Tables\SystemLogsTable;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemLogResource extends Resource
{
    protected static ?string $model = EloquentSystemLog::class;

    protected static ?string $recordTitleAttribute = 'message';

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?int $navigationSort = 70;

    public static function getModelLabel(): string
    {
        return 'запись журнала';
    }

    public static function getPluralModelLabel(): string
    {
        return 'журнал действий';
    }

    public static function getNavigationGroup(): string
    {
        return 'Система';
    }

    public static function getNavigationLabel(): string
    {
        return 'Журнал действий';
    }

    public static function table(Table $table): Table
    {
        return SystemLogsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SystemLogInfolist::configure($schema);
    }

    public static function getNavigationBadge(): string
    {
        return (string) EloquentSystemLog::query()->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'gray';
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListSystemLogs::route('/'),
            'view' => ViewSystemLog::route('/{record}'),
        ];
    }
}
