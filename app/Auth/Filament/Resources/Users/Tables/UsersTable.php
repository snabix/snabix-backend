<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Tables;

use App\Auth\Infrastructure\Models\EloquentUser;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn(EloquentUser $record): string => $record->email),

                TextColumn::make('email_verified_at')
                    ->label('Email')
                    ->badge()
                    ->formatStateUsing(fn($state): string => $state ? 'Подтвержден' : 'Ожидает подтверждения')
                    ->color(fn($state): string => $state ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('id')
                    ->label('UUID')
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Открыть'),
                    EditAction::make()
                        ->label('Редактировать'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранных'),
                ]),
            ])
            ->emptyStateHeading('Пользователи ещё не созданы')
            ->emptyStateDescription('Начните с создания первого пользователя через административную панель.');
    }
}
