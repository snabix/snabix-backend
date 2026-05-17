<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Schemas;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основа объявления')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->description('Каркас объявления: владелец, категория, статус и ключевая информация для публикации.')
                    ->columns()
                    ->schema([
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->options(fn(): array => EloquentUser::query()
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(fn(EloquentUser $user): array => [
                                    $user->id => trim($user->first_name . ' ' . $user->last_name) . ' [' . $user->email . ']',
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('category_id')
                            ->label('Категория')
                            ->options(fn(): array => EloquentCategory::query()
                                ->orderBy('path')
                                ->get()
                                ->mapWithKeys(fn(EloquentCategory $category): array => [
                                    $category->id => $category->full_name . ' [' . $category->catalog_type->label() . ']',
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('type')
                            ->label('Тип объявления')
                            ->options(ListingType::options())
                            ->native(false)
                            ->required(),

                        Select::make('status')
                            ->label('Статус')
                            ->options(ListingStatus::options())
                            ->default(ListingStatus::DRAFT->value)
                            ->native(false)
                            ->required(),

                        Select::make('condition')
                            ->label('Состояние')
                            ->options(ListingCondition::options())
                            ->default(ListingCondition::USED->value)
                            ->native(false)
                            ->visible(fn(Get $get): bool => self::nullableInt($get('type')) !== ListingType::SERVICE->value)
                            ->required(fn(Get $get): bool => self::nullableInt($get('type')) !== ListingType::SERVICE->value),

                        TextInput::make('views_count')
                            ->label('Просмотры')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        TextInput::make('title')
                            ->label('Заголовок')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn(?string $state, Get $get): string => Str::slug($state ?: self::nullableString($get('title')) ?? '')),
                    ]),

                Section::make('Контент и цена')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->columns()
                    ->schema([
                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(8)
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->minValue(0),

                        TextInput::make('currency')
                            ->label('Валюта')
                            ->default('RUB')
                            ->maxLength(3),

                        Toggle::make('is_negotiable')
                            ->label('Допустим торг')
                            ->default(false),

                        Toggle::make('is_featured')
                            ->label('Выделенное объявление')
                            ->default(false),

                        Textarea::make('rejection_reason')
                            ->label('Причина отклонения')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Контакты и сроки')
                    ->icon(Heroicon::OutlinedPhone)
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Контактное имя')
                            ->maxLength(120),
                        TextInput::make('contact_phone')
                            ->label('Контактный телефон')
                            ->tel()
                            ->maxLength(32),
                        TextInput::make('contact_email')
                            ->label('Контактный email')
                            ->email()
                            ->maxLength(255),
                        DateTimePicker::make('published_at')
                            ->label('Опубликовано в')
                            ->seconds(false)
                            ->native(false),
                        DateTimePicker::make('expires_at')
                            ->label('Истекает в')
                            ->seconds(false)
                            ->native(false),
                    ]),

                Section::make('Характеристики объявления')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->description('При необходимости администратор может вручную указать значения подготовленных характеристик.')
                    ->schema([
                        Placeholder::make('attributes_hint')
                            ->label('')
                            ->content('Выбирайте характеристики, подготовленные для нужной категории, и сохраняйте отображаемое значение так, как его увидит пользователь.'),
                        Repeater::make('attributeValues')
                            ->relationship()
                            ->label('Значения характеристик')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('attribute_definition_id')
                                            ->label('Характеристика')
                                            ->options(function (Get $get, ?EloquentListing $record): array {
                                                $categoryId         = $get('../../category_id');
                                                $resolvedCategoryId = is_int($categoryId)
                                                    ? $categoryId
                                                    : (is_string($categoryId) && is_numeric($categoryId)
                                                        ? (int) $categoryId
                                                        : $record?->category_id);

                                                return EloquentCategoryAttributeDefinition::query()
                                                    ->when($resolvedCategoryId !== null, fn($query) => $query->where('category_id', $resolvedCategoryId))
                                                    ->orderBy('sort_order')
                                                    ->orderBy('name')
                                                    ->get()
                                                    ->mapWithKeys(fn(EloquentCategoryAttributeDefinition $definition): array => [
                                                        $definition->id => $definition->name . ' [' . $definition->type->label() . ']',
                                                    ])
                                                    ->all();
                                            })
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('display_value')
                                            ->label('Отображаемое значение')
                                            ->maxLength(255),
                                    ]),
                                Textarea::make('value')
                                    ->label('Сырое значение (JSON)')
                                    ->rows(3)
                                    ->formatStateUsing(fn(mixed $state): string => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '' : self::nullableString($state) ?? '')
                                    ->dehydrateStateUsing(function (?string $state): ?array {
                                        if ($state === null || trim($state) === '') {
                                            return null;
                                        }

                                        $decoded = json_decode($state, true);

                                        return is_array($decoded) ? $decoded : ['value' => $state];
                                    }),
                            ])
                            ->collapsed()
                            ->defaultItems(0),
                    ]),
            ]);
    }

    private static function nullableInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && is_numeric($value) ? (int) $value : null;
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        return (string) $value;
    }
}
