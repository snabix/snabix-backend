<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs\Schemas;

use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SystemLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Сводка')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('level')
                            ->label('Уровень')
                            ->badge()
                            ->formatStateUsing(fn(EloquentSystemLog $record): string => $record->level->label())
                            ->color(fn(EloquentSystemLog $record): string => $record->level->color()),
                        TextEntry::make('category')
                            ->label('Категория'),
                        TextEntry::make('action')
                            ->label('Действие')
                            ->placeholder('-'),
                        TextEntry::make('message')
                            ->label('Сообщение')
                            ->columnSpanFull(),
                    ]),

                Section::make('HTTP-контекст')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('method')
                            ->label('Метод')
                            ->placeholder('-'),
                        TextEntry::make('status_code')
                            ->label('HTTP-статус')
                            ->placeholder('-'),
                        TextEntry::make('route_name')
                            ->label('Имя маршрута')
                            ->placeholder('-'),
                        TextEntry::make('path')
                            ->label('Путь')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('duration_ms')->label('Длительность, мс')->placeholder('-'),
                        TextEntry::make('ip_address')->label('IP-адрес')->placeholder('-'),
                    ]),

                Section::make('Пользователь и окружение')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.email')->label('Пользователь')->placeholder('system'),
                        TextEntry::make('user_agent')->label('User-Agent')->placeholder('-')->columnSpanFull(),
                    ]),

                Section::make('Контекст')
                    ->schema([
                        TextEntry::make('context')
                            ->label('Детали')
                            ->formatStateUsing(
                                fn(mixed $state): string => is_array($state)
                                    ? (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                                    : '-',
                            )
                            ->copyable(),
                    ]),

                Section::make('Системная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')->label('UUID')->copyable(),
                        TextEntry::make('created_at')->translateLabel()->dateTime('d.m.Y H:i:s'),
                        TextEntry::make('updated_at')->translateLabel()->dateTime('d.m.Y H:i:s'),
                    ]),
            ]);
    }
}
