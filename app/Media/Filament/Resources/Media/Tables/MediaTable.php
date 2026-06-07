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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview_url')
                    ->label('Превью')
                    ->getStateUsing(fn(EloquentMedia $record): ?string => $record->media_type === MediaType::IMAGE ? $record->getFullUrl() : null)
                    ->defaultImageUrl(fn(EloquentMedia $record): string => self::filePreviewPlaceholder($record))
                    ->checkFileExistence(false)
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
                        'local'  => 'private/local',
                    ]),
                SelectFilter::make('model_type')
                    ->label('Привязка')
                    ->options(fn(): array => EloquentMedia::query()
                        ->whereNotNull('model_type')
                        ->distinct()
                        ->pluck('model_type')
                        ->mapWithKeys(function (mixed $modelType): array {
                            if (! is_string($modelType)) {
                                return [];
                            }

                            return [$modelType => class_basename($modelType)];
                        })
                        ->all()),
                SelectFilter::make('uploaded_by_admin_id')
                    ->label('Загрузил')
                    ->relationship('uploadedByAdmin', 'email')
                    ->searchable()
                    ->preload(),
                Filter::make('size_range')
                    ->label('Размер файла')
                    ->schema([
                        TextInput::make('size_from_kb')
                            ->label('От, КБ')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('size_to_kb')
                            ->label('До, КБ')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['size_from_kb'] ?? null), fn(Builder $query): Builder => $query->where('size', '>=', (int) $data['size_from_kb'] * 1024))
                            ->when(filled($data['size_to_kb'] ?? null), fn(Builder $query): Builder => $query->where('size', '<=', (int) $data['size_to_kb'] * 1024));
                    }),
                Filter::make('created_between')
                    ->label('Дата загрузки')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Загружено от'),
                        DatePicker::make('created_until')
                            ->label('Загружено до'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(filled($data['created_from'] ?? null), fn(Builder $query): Builder => $query->whereDate('created_at', '>=', $data['created_from']))
                            ->when(filled($data['created_until'] ?? null), fn(Builder $query): Builder => $query->whereDate('created_at', '<=', $data['created_until']));
                    }),
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

    private static function filePreviewPlaceholder(EloquentMedia $record): string
    {
        $label = strtoupper(pathinfo($record->file_name, PATHINFO_EXTENSION) ?: $record->media_type->name);
        $label = mb_substr($label, 0, 5);

        $svg   = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="104" height="104" viewBox="0 0 104 104">
              <rect width="104" height="104" rx="18" fill="#f3f4f6"/>
              <path d="M34 18h25l17 17v51H34z" fill="#ffffff" stroke="#d1d5db" stroke-width="2"/>
              <path d="M59 18v18h17" fill="none" stroke="#d1d5db" stroke-width="2"/>
              <text x="52" y="67" text-anchor="middle" font-family="Arial, sans-serif" font-size="14" font-weight="700" fill="#004643">{$label}</text>
            </svg>
            SVG;

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
