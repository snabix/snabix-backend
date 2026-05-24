<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Regions\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('fullname')
                            ->label('Полное название')
                            ->maxLength(255),
                        TextInput::make('label')
                            ->label('Метка')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('slug')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('type')
                            ->label('Тип')
                            ->maxLength(255),
                        TextInput::make('type_short')
                            ->label('Краткий тип')
                            ->maxLength(255),
                        TextInput::make('district')
                            ->label('Федеральный округ')
                            ->maxLength(255),
                        TextInput::make('unofficial_name')
                            ->label('Неофициальное название')
                            ->maxLength(255),
                        TextInput::make('name_en')
                            ->label('Название на английском')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Коды и статистика')
                    ->columns(3)
                    ->schema([
                        TextInput::make('kladr_id')
                            ->label('КЛАДР')
                            ->required()
                            ->maxLength(32)
                            ->unique(ignoreRecord: true),
                        TextInput::make('fias_guid')
                            ->label('ФИАС GUID')
                            ->maxLength(36)
                            ->unique(ignoreRecord: true),
                        TextInput::make('code')
                            ->label('Код региона')
                            ->maxLength(8)
                            ->unique(ignoreRecord: true),
                        TextInput::make('iso_code')
                            ->label('ISO 3166-2')
                            ->maxLength(16)
                            ->unique(ignoreRecord: true),
                        TextInput::make('okato')
                            ->label('ОКАТО')
                            ->maxLength(32),
                        TextInput::make('oktmo')
                            ->label('ОКТМО')
                            ->maxLength(32),
                        TextInput::make('population')
                            ->label('Население')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('area')
                            ->label('Площадь')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('year_founded')
                            ->label('Год образования')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('sort_order')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),

                Section::make('Склонения и столица')
                    ->columns(2)
                    ->schema([
                        KeyValue::make('name_cases')
                            ->label('Склонения')
                            ->keyLabel('Падеж')
                            ->valueLabel('Значение')
                            ->columnSpanFull(),
                        KeyValue::make('capital_data')
                            ->label('Данные столицы')
                            ->keyLabel('Поле')
                            ->valueLabel('Значение')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
