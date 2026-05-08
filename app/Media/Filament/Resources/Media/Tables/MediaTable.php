<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Tables;

use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview_url')
                    ->label('Превью')
                    ->square()
                    ->size(52)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn(EloquentMedia $record): string => $record->file_name),

                TextColumn::make('media_type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn(EloquentMedia $record): string => $record->media_type->label())
                    ->color(fn(EloquentMedia $record): string => $record->media_type->color())
                    ->sortable(),

                TextColumn::make('visibility')
                    ->label('Доступ')
                    ->badge()
                    ->formatStateUsing(fn(EloquentMedia $record): string => $record->visibility->label())
                    ->color(fn(EloquentMedia $record): string => $record->visibility->color())
                    ->sortable(),

                TextColumn::make('mime_type')
                    ->label('MIME')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('human_readable_size')
                    ->label('Размер')
                    ->sortable(query: fn($query, string $direction) => $query->orderBy('size', $direction)),

                TextColumn::make('disk')
                    ->label('Диск')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('model_type')
                    ->label('Модель')
                    ->formatStateUsing(fn(?string $state): string => $state !== null ? class_basename($state) : 'Без привязки')
                    ->placeholder('Без привязки')
                    ->toggleable(),

                TextColumn::make('model_id')
                    ->label('ID записи')
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('uploadedByAdmin.email')
                    ->label('Загрузил')
                    ->placeholder('system')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('media_type')
                    ->label('Тип')
                    ->options(MediaType::options()),
                SelectFilter::make('visibility')
                    ->label('Доступ')
                    ->options(MediaVisibility::options()),
                SelectFilter::make('disk')
                    ->label('Диск')
                    ->options([
                        'public' => 'public',
                        'local' => 'private/local',
                    ]),
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
                DeleteBulkAction::make()
                    ->label('Удалить выбранные'),
            ])
            ->emptyStateHeading('Медиафайлы пока не загружены')
            ->emptyStateDescription('Загрузите первый файл через административную панель.');
    }
}
