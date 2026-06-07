<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Schemas;

use App\News\Infrastructure\Models\EloquentNewsPost;
use App\News\Infrastructure\Models\EloquentNewsPostBlock;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class NewsPostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Обложка')
                    ->schema([
                        TextEntry::make('cover_preview')
                            ->label('Превью')
                            ->state(fn(EloquentNewsPost $record): HtmlString => self::coverPreview($record))
                            ->html(),
                    ]),

                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')->label('Заголовок'),
                        TextEntry::make('slug')->label('Slug')->copyable(),
                        TextEntry::make('category')->label('Категория'),
                        TextEntry::make('eyebrow')->label('Надзаголовок')->placeholder('—'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->formatStateUsing(fn(EloquentNewsPost $record): string => $record->status->label())
                            ->color(fn(EloquentNewsPost $record): string => $record->status->color()),
                        IconEntry::make('is_featured')->label('Главный материал')->boolean(),
                        TextEntry::make('reading_time')->label('Время чтения')->placeholder('—'),
                        TextEntry::make('views_count')->label('Просмотры'),
                        TextEntry::make('published_at')->label('Опубликовано')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('authorAdmin.email')->label('Автор')->placeholder('system'),
                        TextEntry::make('description')->label('Описание')->columnSpanFull(),
                        TextEntry::make('thesis')->label('Тезис')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Блоки контента')
                    ->schema([
                        RepeatableEntry::make('blocks')
                            ->label('Блоки')
                            ->schema([
                                TextEntry::make('sort_order')->label('Порядок'),
                                TextEntry::make('type')
                                    ->label('Тип')
                                    ->badge()
                                    ->formatStateUsing(fn(EloquentNewsPostBlock $record): string => $record->type->label())
                                    ->color(fn(EloquentNewsPostBlock $record): string => $record->type->color()),
                                TextEntry::make('blockMedia.file_name')->label('Медиа')->placeholder('—'),
                                TextEntry::make('data')
                                    ->label('Данные')
                                    ->formatStateUsing(fn(EloquentNewsPostBlock $record): string => json_encode($record->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    private static function coverPreview(EloquentNewsPost $record): HtmlString
    {
        if ($record->coverMedia === null) {
            return new HtmlString('<div style="border:1px dashed #d1d5db;border-radius:18px;padding:24px;color:#6b7280;">Обложка не выбрана</div>');
        }

        $url      = e($record->coverMedia->getFullUrl());
        $fileName = e($record->coverMedia->file_name);

        return new HtmlString(<<<HTML
            <div style="display:flex;justify-content:center;border-radius:24px;background:#f8fafc;padding:24px;">
                <a href="{$url}" target="_blank" rel="noopener noreferrer" style="display:block;max-width:100%;">
                    <img src="{$url}" alt="{$fileName}" style="display:block;max-height:420px;max-width:100%;border-radius:18px;object-fit:contain;box-shadow:0 18px 42px rgba(15,23,42,.16);" />
                </a>
            </div>
            HTML);
    }
}
