<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Schemas;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.email')->label('Пользователь'),
                        TextEntry::make('category.full_name')->label('Категория'),
                        TextEntry::make('type')->formatStateUsing(fn(EloquentListing $record): string => $record->type->label()),
                        TextEntry::make('status')->formatStateUsing(fn(EloquentListing $record): string => $record->status->label()),
                        TextEntry::make('condition')->formatStateUsing(fn(EloquentListing $record): string => $record->condition->label()),
                        TextEntry::make('title')->label('Заголовок'),
                        TextEntry::make('slug')->label('Slug'),
                        TextEntry::make('price')->label('Цена'),
                        TextEntry::make('currency')->label('Валюта'),
                        IconEntry::make('is_negotiable')->label('Торг')->boolean(),
                        IconEntry::make('is_featured')->label('Выделенное')->boolean(),
                        TextEntry::make('views_count')->label('Просмотры'),
                    ]),

                Section::make('Контакты и публикация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('contact_name')->label('Контактное имя')->placeholder('—'),
                        TextEntry::make('contact_phone')->label('Контактный телефон')->placeholder('—'),
                        TextEntry::make('contact_email')->label('Контактный email')->placeholder('—'),
                        TextEntry::make('published_at')->label('Опубликовано в')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('expires_at')->label('Истекает в')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('rejection_reason')->label('Причина отклонения')->placeholder('—'),
                    ]),

                Section::make('Описание')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),
                    ]),

                Section::make('Характеристики')
                    ->schema([
                        RepeatableEntry::make('attributeValues')
                            ->label('Значения характеристик')
                            ->schema([
                                TextEntry::make('attributeDefinition.name')->label('Характеристика'),
                                TextEntry::make('display_value')->label('Отображаемое значение')->placeholder('—'),
                                TextEntry::make('value')
                                    ->label('Сырое значение')
                                    ->formatStateUsing(fn(EloquentListingAttributeValue $record): string => $record->value !== null ? json_encode($record->value, JSON_UNESCAPED_UNICODE) ?: '' : '—'),
                            ]),
                    ]),
            ]);
    }
}
