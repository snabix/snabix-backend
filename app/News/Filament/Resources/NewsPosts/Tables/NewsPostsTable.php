<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Tables;

use App\News\Domain\Enums\NewsPostStatus;
use App\News\Filament\Resources\NewsPosts\NewsPostResource;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class NewsPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with(['coverMedia', 'authorAdmin']))
            ->defaultSort('published_at', 'desc')
            ->recordUrl(fn(EloquentNewsPost $record): string => NewsPostResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')
                    ->label('Материал')
                    ->html()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state, EloquentNewsPost $record): HtmlString => new HtmlString(sprintf(
                        '<div style="display:grid;gap:4px;"><span style="font-weight:700;color:#0f172a;">%s</span><span style="font-size:12px;color:#64748b;">%s</span></div>',
                        e($state),
                        e($record->slug),
                    ))),
                TextColumn::make('category')
                    ->label('Категория')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn(EloquentNewsPost $record): string => $record->status->label())
                    ->color(fn(EloquentNewsPost $record): string => $record->status->color()),
                IconColumn::make('is_featured')
                    ->label('Главный')
                    ->boolean(),
                TextColumn::make('views_count')
                    ->label('Просмотры')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label('Опубликовано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('authorAdmin.email')
                    ->label('Автор')
                    ->placeholder('system'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(NewsPostStatus::options()),
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(fn(): array => EloquentNewsPost::query()
                        ->select('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
                SelectFilter::make('is_featured')
                    ->label('Главный материал')
                    ->options([
                        '1' => 'Только главные',
                        '0' => 'Обычные',
                    ]),
                Filter::make('published_between')
                    ->label('Дата публикации')
                    ->schema([
                        DatePicker::make('published_from')
                            ->label('Опубликовано от'),
                        DatePicker::make('published_until')
                            ->label('Опубликовано до'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['published_from'] ?? null), fn(Builder $query): Builder => $query->whereDate('published_at', '>=', $data['published_from']))
                            ->when(filled($data['published_until'] ?? null), fn(Builder $query): Builder => $query->whereDate('published_at', '<=', $data['published_until']));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
