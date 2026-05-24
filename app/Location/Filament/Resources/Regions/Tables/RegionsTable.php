<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Tables;

use App\Location\Filament\Resources\Regions\RegionResource;
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

class RegionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->withCount('cities'))
            ->defaultSort('sort_order')
            ->recordUrl(fn(EloquentRegion $record): string => RegionResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->description(fn(EloquentRegion $record): ?string => $record->fullname),
                TextColumn::make('district')
                    ->label('Федеральный округ')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Код')
                    ->badge()
                    ->sortable(),
                TextColumn::make('iso_code')
                    ->label('ISO')
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('cities_count')
                    ->label('Городов')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('population')
                    ->label('Население')
                    ->formatStateUsing(fn(?int $state): string => self::formatNumber($state))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Активен' : 'Скрыт')
                    ->color(fn(bool $state): string => $state ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('district')
                    ->label('Федеральный округ')
                    ->options(fn(): array => EloquentRegion::query()
                        ->whereNotNull('district')
                        ->orderBy('district')
                        ->pluck('district', 'district')
                        ->all()),
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
            ->emptyStateHeading('Регионы ещё не импортированы')
            ->emptyStateDescription('Запустите команду location:import-russia или создайте первый регион вручную.');
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
