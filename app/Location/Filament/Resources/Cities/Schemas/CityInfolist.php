<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->translateLabel(),
                        TextEntry::make('region.name')
                            ->label('Регион'),
                        TextEntry::make('human_readable_population')
                            ->label('Население')
                            ->placeholder('-'),
                        IconEntry::make('is_capital')
                            ->label('Столица региона')
                            ->boolean(),
                        IconEntry::make('is_dual_name')
                            ->label('Неуникальное название')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Активен')
                            ->boolean(),
                        TextEntry::make('year_founded')
                            ->label('Год основания')
                            ->placeholder('-'),
                        TextEntry::make('year_city_status')
                            ->label('Год статуса города')
                            ->placeholder('-'),
                        TextEntry::make('zip')
                            ->label('Индекс')
                            ->placeholder('-'),
                    ]),

                Section::make('Коды и координаты')
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
                        TextEntry::make('coordinates')
                            ->label('Координаты')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('slug')
                            ->translateLabel()
                            ->copyable(),
                    ]),

                Section::make('Склонения и часовой пояс')
                    ->schema([
                        KeyValueEntry::make('name_cases')
                            ->label('Склонения'),
                        KeyValueEntry::make('timezone')
                            ->label('Часовой пояс'),
                    ]),
            ]);
    }
}
