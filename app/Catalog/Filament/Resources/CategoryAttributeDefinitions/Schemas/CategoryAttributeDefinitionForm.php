<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Schemas;

use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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

class CategoryAttributeDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Category attribute basics'))
                            ->icon(Heroicon::OutlinedSparkles)
                            ->description('Создайте готовое поле, которое пользователь будет заполнять при создании объявления в выбранной категории.')
                            ->columnSpan([
                                'default' => 12,
                                'lg'      => 8,
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Attribute name')
                                    ->translateLabel()
                                    ->placeholder('Например, Бренд или Материал')
                                    ->prefixIcon(Heroicon::OutlinedQueueList)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                        if (blank($get('slug'))) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    })
                                    ->required()
                                    ->maxLength(255),

                                Grid::make()
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Category')
                                            ->translateLabel()
                                            ->placeholder('Выберите категорию')
                                            ->options(self::categoryOptions(...))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->native(false)
                                            ->helperText('Выберите конкретную категорию, в форме которой должна появиться эта характеристика.'),

                                        Select::make('type')
                                            ->label('Attribute type')
                                            ->translateLabel()
                                            ->options(CategoryAttributeType::options())
                                            ->native(false)
                                            ->searchable()
                                            ->live()
                                            ->default(CategoryAttributeType::TEXT->value)
                                            ->required()
                                            ->helperText('Тип характеристики определяет, как значение будет проверяться в форме объявления.'),

                                        TextInput::make('slug')
                                            ->label('Attribute code')
                                            ->translateLabel()
                                            ->disabled()
                                            ->placeholder('Будет создан из названия')
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                                $name = $get('name');

                                                return filled($state)
                                                    ? Str::slug($state)
                                                    : Str::slug(is_string($name) ? $name : '');
                                            })
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('sort_order')
                                            ->label('Sort order')
                                            ->translateLabel()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->prefixIcon(Heroicon::OutlinedBars3BottomLeft),

                                    ]),

                                TextInput::make('unit')
                                    ->translateLabel()
                                    ->placeholder('Например, см, кг, кВт')
                                    ->prefixIcon(Heroicon::OutlinedScale)
                                    ->helperText('Необязательно. Используйте только если значение нужно показывать с единицей измерения.')
                                    ->maxLength(32),

                                TextInput::make('group_name')
                                    ->label('Attribute group')
                                    ->translateLabel()
                                    ->placeholder('Например, Основное или Размеры')
                                    ->prefixIcon(Heroicon::OutlinedRectangleGroup)
                                    ->helperText('Помогает группировать связанные характеристики внутри формы объявления.')
                                    ->maxLength(120),
                            ]),

                        Section::make(__('Behavior in listing forms'))
                            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                            ->description('Эти настройки определяют поведение характеристики в пользовательской форме объявления.')
                            ->columnSpan([
                                'default' => 12,
                                'lg'      => 4,
                            ])
                            ->columns()
                            ->schema([
                                Toggle::make('is_required')
                                    ->label('Attribute is required')
                                    ->translateLabel()
                                    ->default(false)
                                    ->inline(false),

                                Toggle::make('is_filterable')
                                    ->label('Attribute is filterable')
                                    ->translateLabel()
                                    ->default(false)
                                    ->inline(false),

                                Toggle::make('show_in_card')
                                    ->label('Show in listing card')
                                    ->translateLabel()
                                    ->default(false)
                                    ->inline(false),

                                Toggle::make('applies_to_children')
                                    ->label('Apply to child categories')
                                    ->translateLabel()
                                    ->default(true)
                                    ->inline(false),

                                Toggle::make('is_active')
                                    ->label('Attribute is active')
                                    ->translateLabel()
                                    ->default(true)
                                    ->inline(false),
                            ]),

                        Section::make(__('Options and description'))
                            ->icon(Heroicon::OutlinedListBullet)
                            ->description('Варианты нужны только для характеристик с фиксированным набором значений, например бренд, размер или состояние.')
                            ->columnSpanFull()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('placeholder')
                                            ->label('Field placeholder')
                                            ->translateLabel()
                                            ->placeholder('Например, укажите точный бренд')
                                            ->helperText('Короткая подсказка внутри поля ввода для пользователя.')
                                            ->maxLength(255),

                                        TagsInput::make('options')
                                            ->label('Attribute options')
                                            ->translateLabel()
                                            ->placeholder('Добавляйте каждое допустимое значение отдельно')
                                            ->visible(function (Get $get): bool {
                                                $type      = $get('type');

                                                $typeValue = is_int($type)
                                                    ? $type
                                                    : (is_numeric($type) ? (int) $type : null);

                                                return in_array($typeValue, [CategoryAttributeType::SELECT->value, CategoryAttributeType::MULTISELECT->value], true);
                                            })
                                            ->helperText('Эти значения станут готовыми вариантами выбора для пользователя.')
                                            ->reorderable()
                                            ->splitKeys(['Tab', 'Enter'])
                                            ->nestedRecursiveRules(['string', 'max:255']),

                                        KeyValue::make('default_value')
                                            ->label('Default value')
                                            ->translateLabel()
                                            ->helperText('Необязательное значение по умолчанию для будущей формы объявления.')
                                            ->columnSpanFull(),

                                        KeyValue::make('dependency_rules')
                                            ->label('Dependency rules')
                                            ->translateLabel()
                                            ->helperText('Необязательно. Используйте для правил видимости: от какой другой характеристики зависит отображение этого поля.')
                                            ->columnSpanFull(),

                                        Textarea::make('help_text')
                                            ->label('Field help text')
                                            ->translateLabel()
                                            ->placeholder('Объясните, что именно пользователь должен указать здесь.')
                                            ->rows(3)
                                            ->helperText('Дополнительная подсказка, которую можно показать под полем в форме объявления.')
                                            ->maxLength(2000),

                                        Textarea::make('description')
                                            ->translateLabel()
                                            ->placeholder('Опишите назначение этой характеристики для команды.')
                                            ->rows(4)
                                            ->helperText('Внутреннее описание, которое позже можно переиспользовать в формах объявления.')
                                            ->maxLength(2000),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(): array
    {
        return EloquentCategory::query()
            ->orderBy('path')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn(EloquentCategory $category): array => [
                $category->id => sprintf('%s [%s]', $category->full_name, $category->catalog_type->label()),
            ])
            ->all();
    }
}
