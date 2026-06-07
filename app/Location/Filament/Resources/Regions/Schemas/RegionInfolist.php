<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Schemas;

use App\Location\Infrastructure\Models\EloquentRegion;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('display_name')
                            ->label('Название')
                            ->columnSpan(2),
                        TextEntry::make('district')
                            ->label('Федеральный округ')
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Тип')
                            ->placeholder('-'),
                        TextEntry::make('code')
                            ->label('Код региона')
                            ->placeholder('-'),
                        TextEntry::make('iso_code')
                            ->label('ISO 3166-2')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('human_readable_population')
                            ->label('Население')
                            ->placeholder('-'),
                        TextEntry::make('area')
                            ->label('Площадь, км²')
                            ->placeholder('-'),
                        TextEntry::make('cities_count')
                            ->label('Городов')
                            ->state(fn(EloquentRegion $record): int => $record->cities()->count()),
                        IconEntry::make('is_active')
                            ->label('Активен')
                            ->boolean(),
                    ]),

                Section::make('Коды')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('kladr_id')
                            ->label('КЛАДР')
                            ->copyable(),
                        TextEntry::make('fias_guid')
                            ->label('ФИАС GUID')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('okato')
                            ->label('ОКАТО')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('oktmo')
                            ->label('ОКТМО')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('slug')
                            ->translateLabel()
                            ->copyable(),
                        TextEntry::make('label')
                            ->label('Метка')
                            ->copyable(),
                    ]),

                Section::make('Склонения и столица')
                    ->schema([
                        KeyValueEntry::make('name_cases')
                            ->label('Склонения')
                            ->columnSpanFull(),
                        KeyValueEntry::make('capital_data')
                            ->label('Данные столицы')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
