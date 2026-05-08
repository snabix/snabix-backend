<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Schemas;

use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Превью')
                    ->schema([
                        ImageEntry::make('preview_url')
                            ->label('Файл')
                            ->visible(fn(EloquentMedia $record): bool => $record->media_type === MediaType::IMAGE),
                    ]),

                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('file_name')->label('Имя файла')->copyable(),
                        TextEntry::make('media_type')
                            ->label('Тип')
                            ->badge()
                            ->formatStateUsing(fn(EloquentMedia $record): string => $record->media_type->label())
                            ->color(fn(EloquentMedia $record): string => $record->media_type->color()),
                        TextEntry::make('visibility')
                            ->label('Доступ')
                            ->badge()
                            ->formatStateUsing(fn(EloquentMedia $record): string => $record->visibility->label())
                            ->color(fn(EloquentMedia $record): string => $record->visibility->color()),
                        TextEntry::make('mime_type')->label('MIME')->placeholder('-'),
                        TextEntry::make('human_readable_size')->label('Размер'),
                        TextEntry::make('disk')->label('Диск'),
                        TextEntry::make('collection_name')->label('Коллекция'),
                        TextEntry::make('description')
                            ->label('Описание')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Привязка')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('model_type')
                            ->label('Модель')
                            ->formatStateUsing(fn(?string $state): string => $state !== null ? class_basename($state) : 'Без привязки'),
                        TextEntry::make('model_id')
                            ->label('ID записи')
                            ->placeholder('-')
                            ->copyable(),
                    ]),

                Section::make('Системная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')->label('ID')->copyable(),
                        TextEntry::make('uuid')->label('UUID')->copyable(),
                        TextEntry::make('uploadedByAdmin.email')->label('Загрузил')->placeholder('system'),
                        TextEntry::make('created_at')->translateLabel()->dateTime('d.m.Y H:i:s'),
                        TextEntry::make('updated_at')->translateLabel()->dateTime('d.m.Y H:i:s'),
                    ]),
            ]);
    }
}
