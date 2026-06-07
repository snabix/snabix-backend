<?php

declare(strict_types=1);

namespace App\Location\Filament\Resources\Cities\Schemas;

use App\Location\Infrastructure\Models\EloquentRegion;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->columns(2)
                    ->schema([
                        Select::make('region_id')
                            ->label('Регион')
                            ->options(fn(): array => EloquentRegion::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                        TextInput::make('name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_alt')
                            ->label('Название без Ё')
                            ->maxLength(255),
                        TextInput::make('label')
                            ->label('Метка')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('type')
                            ->label('Тип')
                            ->maxLength(255),
                        TextInput::make('type_short')
                            ->label('Краткий тип')
                            ->maxLength(255),
                        TextInput::make('name_en')
                            ->label('Название на английском')
                            ->maxLength(255),
                        Toggle::make('is_capital')
                            ->label('Столица региона')
                            ->inline(false),
                        Toggle::make('is_dual_name')
                            ->label('Неуникальное название')
                            ->inline(false),
                        Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Коды, координаты и статистика')
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
                        TextInput::make('okato')
                            ->label('ОКАТО')
                            ->maxLength(32),
                        TextInput::make('oktmo')
                            ->label('ОКТМО')
                            ->maxLength(32),
                        TextInput::make('zip')
                            ->label('Индекс')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('population')
                            ->label('Население')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('year_founded')
                            ->label('Год основания')
                            ->maxLength(255),
                        TextInput::make('year_city_status')
                            ->label('Год статуса города')
                            ->maxLength(255),
                        TextInput::make('lat')
                            ->label('Широта')
                            ->numeric(),
                        TextInput::make('lon')
                            ->label('Долгота')
                            ->numeric(),
                        TextInput::make('sort_order')
                            ->label('Сортировка')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),

                Section::make('Склонения и часовой пояс')
                    ->schema([
                        KeyValue::make('name_cases')
                            ->label('Склонения')
                            ->keyLabel('Падеж')
                            ->valueLabel('Значение'),
                        KeyValue::make('timezone')
                            ->label('Часовой пояс')
                            ->keyLabel('Поле')
                            ->valueLabel('Значение'),
                    ]),
            ]);
    }
}
