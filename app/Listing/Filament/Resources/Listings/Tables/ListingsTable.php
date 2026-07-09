<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Tables;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Filament\Resources\Listings\Actions\ListingModerationActions;
use App\Listing\Filament\Resources\Listings\ListingResource;
use App\Listing\Infrastructure\Models\EloquentListing;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with(['user', 'category']))
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn(EloquentListing $record): string => ListingResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('title')
                    ->label('Объявление')
                    ->html()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state, EloquentListing $record): HtmlString => new HtmlString(sprintf(
                        '<div style="display:grid;gap:4px;"><span style="font-weight:700;color:#0f172a;">%s</span><span style="font-size:12px;color:#64748b;">%s</span></div>',
                        e($state),
                        e($record->slug),
                    ))),
                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->searchable(),
                TextColumn::make('category.full_name')
                    ->label('Категория')
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn(EloquentListing $record): string => $record->type->label())
                    ->color(fn(EloquentListing $record): string => $record->type === ListingType::PRODUCT ? 'info' : 'success'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(EloquentListing $record): string => $record->status->label())
                    ->color(fn(EloquentListing $record): string => match ($record->status) {
                        ListingStatus::DRAFT          => 'gray',
                        ListingStatus::PENDING_REVIEW => 'warning',
                        ListingStatus::PUBLISHED      => 'success',
                        ListingStatus::REJECTED       => 'danger',
                        ListingStatus::ARCHIVED       => 'primary',
                    }),
                TextColumn::make('price')
                    ->label('Цена')
                    ->formatStateUsing(fn(?int $state, EloquentListing $record): string => $state !== null ? number_format($state, 0, '.', ' ') . ' ' . $record->currency : '—'),
                IconColumn::make('is_featured')
                    ->label('Топ')
                    ->boolean(),
                TextColumn::make('views_count')
                    ->label('Просмотры')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Опубликовано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(ListingType::options()),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ListingStatus::options()),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_featured')
                    ->label('Продвижение')
                    ->options([
                        '1' => 'Только топ',
                        '0' => 'Без продвижения',
                    ]),
                Filter::make('price_range')
                    ->label('Цена')
                    ->schema([
                        TextInput::make('price_from')
                            ->label('Цена от')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('price_to')
                            ->label('Цена до')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['price_from'] ?? null), fn(Builder $query): Builder => $query->where('price', '>=', (int) $data['price_from']))
                            ->when(filled($data['price_to'] ?? null), fn(Builder $query): Builder => $query->where('price', '<=', (int) $data['price_to']));
                    }),
                Filter::make('created_between')
                    ->label('Дата создания')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Создано от'),
                        DatePicker::make('created_until')
                            ->label('Создано до'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['created_from'] ?? null), fn(Builder $query): Builder => $query->whereDate('created_at', '>=', $data['created_from']))
                            ->when(filled($data['created_until'] ?? null), fn(Builder $query): Builder => $query->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ...ListingModerationActions::make(),
                    ViewAction::make()
                        ->label('Открыть'),
                    EditAction::make()
                        ->label('Корректировать'),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
