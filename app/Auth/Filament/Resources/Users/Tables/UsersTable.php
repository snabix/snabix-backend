<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Tables;

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
                TextColumn::make('first_name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('last_name')
                    ->label('Фамилия')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone_number')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->translateLabel()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('is_active')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Активен' : 'Отключен')
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger'),

                TextColumn::make('email_verified_at')
                    ->translateLabel()
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
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->translateLabel()
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
