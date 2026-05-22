<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Tables;

use App\Location\Filament\Resources\Cities\CityResource;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with('region'))
            ->defaultSort('name')
            ->recordUrl(fn(EloquentCity $record): string => CityResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->description(fn(EloquentCity $record): ?string => $record->name_alt),
                TextColumn::make('region.name')
                    ->label('Регион')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('population')
                    ->label('Население')
                    ->formatStateUsing(fn(?int $state): string => self::formatNumber($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('zip')
                    ->label('Индекс')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_capital')
                    ->label('Столица')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Да' : 'Нет')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('is_dual_name')
                    ->label('Дубль')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Да' : 'Нет')
                    ->color(fn(bool $state): string => $state ? 'warning' : 'gray')
                    ->toggleable(),
                TextColumn::make('kladr_id')
                    ->label('КЛАДР')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('region_id')
                    ->label('Регион')
                    ->options(fn(): array => EloquentRegion::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_capital')
                    ->label('Столица региона')
                    ->options([
                        '1' => 'Да',
                        '0' => 'Нет',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        '1' => 'Активные',
                        '0' => 'Скрытые',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->translateLabel(),
                    EditAction::make()
                        ->translateLabel(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Удалить выбранные'),
            ])
            ->emptyStateHeading('Города ещё не импортированы')
            ->emptyStateDescription('Запустите команду location:import-russia или создайте первый город вручную.');
    }

    private static function formatNumber(?int $state): string
    {
        if ($state === null) {
            return '-';
        }

        $formatted = Number::format($state, locale: 'ru');

        return is_string($formatted) ? $formatted : (string) $state;
    }
}
