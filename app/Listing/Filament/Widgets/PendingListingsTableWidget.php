<?php

declare(strict_types=1);

namespace App\Listing\Filament\Widgets;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Filament\Resources\Listings\ListingResource;
use App\Listing\Infrastructure\Models\EloquentListing;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingListingsTableWidget extends TableWidget
{
    use HasWidgetShield;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Очередь модерации')
            ->query(
                EloquentListing::query()
                    ->with(['user', 'category'])
                    ->where('status', ListingStatus::PENDING_REVIEW->value)
                    ->latest(),
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Объявление')
                    ->searchable()
                    ->sortable()
                    ->description(fn(EloquentListing $record): string => $record->slug),
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->searchable(),
                TextColumn::make('category.full_name')
                    ->label('Категория')
                    ->wrap(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->formatStateUsing(fn(?int $state, EloquentListing $record): string => $state !== null ? number_format($state, 0, '.', ' ') . ' ' . $record->currency : '—'),
                TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordUrl(fn(EloquentListing $record): string => ListingResource::getUrl('edit', ['record' => $record]))
            ->modifyQueryUsing(fn(Builder $query): Builder => $query)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn(EloquentListing $record): string => ListingResource::getUrl('view', ['record' => $record])),
                    EditAction::make()
                        ->url(fn(EloquentListing $record): string => ListingResource::getUrl('edit', ['record' => $record])),
                ]),
            ]);
    }
}
