<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Schemas;

use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MediaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Превью')
                    ->schema([
                        TextEntry::make('media_preview')
                            ->label('Файл')
                            ->state(fn(EloquentMedia $record): HtmlString => self::renderMediaPreview($record))
                            ->html(),
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

    private static function renderMediaPreview(EloquentMedia $record): HtmlString
    {
        $url      = e($record->getFullUrl());
        $fileName = e($record->file_name);
        $mimeType = e($record->mime_type ?? 'unknown');
        $size     = e($record->human_readable_size);

        if ($record->media_type === MediaType::IMAGE) {
            return new HtmlString(<<<HTML
                <div style="display:flex;justify-content:center;border-radius:24px;background:#f8fafc;padding:24px;">
                    <a href="{$url}" target="_blank" rel="noopener noreferrer" style="display:block;max-width:100%;">
                        <img src="{$url}" alt="{$fileName}" style="display:block;max-height:520px;max-width:100%;border-radius:18px;object-fit:contain;box-shadow:0 18px 42px rgba(15,23,42,.16);" />
                    </a>
                </div>
                HTML);
        }

        if ($record->media_type === MediaType::VIDEO) {
            return new HtmlString(<<<HTML
                <video controls style="display:block;width:100%;max-height:520px;border-radius:18px;background:#020617;">
                    <source src="{$url}" type="{$mimeType}" />
                </video>
                HTML);
        }

        if ($record->mime_type === 'application/pdf') {
            return new HtmlString(<<<HTML
                <div style="display:flex;align-items:center;gap:18px;border:1px solid #e5e7eb;border-radius:22px;background:#f8fafc;padding:20px;">
                    <div style="display:grid;width:64px;height:64px;place-items:center;border-radius:18px;background:#b91c1c;color:#ffffff;font-weight:900;">PDF</div>
                    <div style="min-width:0;flex:1;">
                        <div style="font-weight:800;color:#111827;">{$fileName}</div>
                        <div style="margin-top:4px;color:#6b7280;font-size:13px;">{$mimeType} · {$size}</div>
                        <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:10px;">
                            <a href="{$url}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;justify-content:center;border-radius:12px;background:#004643;color:#fafafa;padding:10px 14px;font-weight:800;text-decoration:none;">Открыть PDF</a>
                            <a href="{$url}" download style="display:inline-flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid #d1d5db;color:#004643;padding:10px 14px;font-weight:800;text-decoration:none;">Скачать</a>
                        </div>
                        <div style="margin-top:10px;color:#6b7280;font-size:12px;">Встроенный просмотр PDF может блокироваться браузером, поэтому файл открывается отдельно.</div>
                    </div>
                </div>
                HTML);
        }

        return new HtmlString(<<<HTML
            <div style="display:flex;align-items:center;gap:16px;border:1px solid #e5e7eb;border-radius:20px;background:#f8fafc;padding:18px;">
                <div style="display:grid;width:56px;height:56px;place-items:center;border-radius:16px;background:#004643;color:#fafafa;font-weight:800;">FILE</div>
                <div style="min-width:0;">
                    <div style="font-weight:800;color:#111827;">{$fileName}</div>
                    <div style="margin-top:4px;color:#6b7280;font-size:13px;">{$mimeType} · {$size}</div>
                    <a href="{$url}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;margin-top:10px;color:#004643;font-weight:700;text-decoration:none;">Открыть файл</a>
                </div>
            </div>
            HTML);
    }
}
