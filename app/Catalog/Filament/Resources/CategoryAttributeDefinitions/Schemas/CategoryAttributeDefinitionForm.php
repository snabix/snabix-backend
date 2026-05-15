<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Schemas;

use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CategoryAttributeDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Category attribute basics'))
                    ->icon(Heroicon::OutlinedSparkles)
                    ->description(__('Define a reusable ready-made field that users will fill in when creating an ad in a selected category.'))
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->translateLabel()
                            ->placeholder(__('Choose a category'))
                            ->options(self::categoryOptions(...))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->helperText(__('Pick the exact category whose form should receive this prepared field.')),

                        ToggleButtons::make('type')
                            ->label('Attribute type')
                            ->translateLabel()
                            ->options(CategoryAttributeType::options())
                            ->inline()
                            ->grouped()
                            ->default(CategoryAttributeType::TEXT->value)
                            ->required()
                            ->helperText(__('The field type controls how the value will be validated in listing forms.')),

                        TextInput::make('name')
                            ->label('Attribute name')
                            ->translateLabel()
                            ->placeholder(__('For example, Brand or Material'))
                            ->prefixIcon(Heroicon::OutlinedQueueList)
                            ->hint(__('Visible to the user in the listing form'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Attribute code')
                            ->translateLabel()
                            ->placeholder(__('Will be generated from the name'))
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->hint(__('Technical identifier for API and saved values'))
                            ->dehydrateStateUsing(function (?string $state, Get $get): string {
                                $name = $get('name');

                                return filled($state)
                                    ? Str::slug($state)
                                    : Str::slug(is_string($name) ? $name : '');
                            })
                            ->required()
                            ->maxLength(255),

                        TextInput::make('unit')
                            ->translateLabel()
                            ->placeholder(__('For example, cm, kg, kW'))
                            ->prefixIcon(Heroicon::OutlinedScale)
                            ->helperText(__('Optional, only when the value should be displayed with a measurement unit.'))
                            ->maxLength(32),

                        TextInput::make('group_name')
                            ->label('Attribute group')
                            ->translateLabel()
                            ->placeholder(__('For example, Main or Dimensions'))
                            ->prefixIcon(Heroicon::OutlinedRectangleGroup)
                            ->helperText(__('Helps group related prepared fields inside the listing form.'))
                            ->maxLength(120),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->translateLabel()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->prefixIcon(Heroicon::OutlinedBars3BottomLeft),
                    ]),

                Section::make(__('Behavior in listing forms'))
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->description(__('These settings determine how the field behaves for users when they fill out a ready-made ad form.'))
                    ->columns(2)
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
                    ->description(__('Options are needed only for fields with a fixed set of values, such as a brand, size, or condition.'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('placeholder')
                                    ->label('Field placeholder')
                                    ->translateLabel()
                                    ->placeholder(__('For example, Enter the exact brand'))
                                    ->helperText(__('Short hint inside the user input field.'))
                                    ->maxLength(255),

                                TagsInput::make('options')
                                    ->label('Attribute options')
                                    ->translateLabel()
                                    ->placeholder(__('Add each allowed value separately'))
                                    ->visible(function (Get $get): bool {
                                        $type      = $get('type');

                                        $typeValue = is_int($type)
                                            ? $type
                                            : (is_numeric($type) ? (int) $type : null);

                                        return in_array($typeValue, [CategoryAttributeType::SELECT->value, CategoryAttributeType::MULTISELECT->value], true);
                                    })
                                    ->helperText(__('These values will become ready-made choices for the user.'))
                                    ->reorderable()
                                    ->splitKeys(['Tab', 'Enter'])
                                    ->nestedRecursiveRules(['string', 'max:255']),

                                KeyValue::make('default_value')
                                    ->label('Default value')
                                    ->translateLabel()
                                    ->helperText(__('Optional prepared value or payload for the future listing form.'))
                                    ->columnSpanFull(),

                                Textarea::make('help_text')
                                    ->label('Field help text')
                                    ->translateLabel()
                                    ->placeholder(__('Explain what exactly the user should enter here.'))
                                    ->rows(3)
                                    ->helperText(__('Additional explanation that can be shown right under the field in the listing form.'))
                                    ->maxLength(2000),

                                Textarea::make('description')
                                    ->translateLabel()
                                    ->placeholder(__('Explain what exactly the user should enter in this field.'))
                                    ->rows(4)
                                    ->helperText(__('Short guidance that can be shown to the team and later reused in listing forms.'))
                                    ->maxLength(2000),
                            ]),
                    ]),

                Section::make(__('Preview for the team'))
                    ->icon(Heroicon::OutlinedEye)
                    ->description(__('Helps immediately understand how this attribute will be used when a user creates an ad from their account.'))
                    ->visible(fn(?EloquentCategoryAttributeDefinition $record): bool => $record !== null)
                    ->schema([
                        Placeholder::make('attribute_preview')
                            ->label('Attribute preview')
                            ->translateLabel()
                            ->content(fn(?EloquentCategoryAttributeDefinition $record): HtmlString => self::renderPreview($record)),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
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

    private static function renderPreview(?EloquentCategoryAttributeDefinition $record): HtmlString
    {
        if ($record === null) {
            return new HtmlString('');
        }

        $options = is_array($record->options) && $record->options !== []
            ? implode(', ', array_map(static function (mixed $option): string {
                if (is_scalar($option)) {
                    return (string) $option;
                }

                return json_encode($option, JSON_UNESCAPED_UNICODE) ?: '';
            }, $record->options))
            : __('No fixed options');

        return new HtmlString(sprintf(
            '<div style="display:grid;gap:12px;padding:16px 18px;border:1px solid #e5e7eb;border-radius:18px;background:linear-gradient(135deg,#ffffff 0%%,#f8fafc 100%%);">
                <div style="display:flex;justify-content:space-between;gap:16px;align-items:start;">
                    <div>
                        <div style="font-size:14px;color:#64748b;">%s</div>
                        <div style="font-size:18px;font-weight:700;color:#0f172a;">%s</div>
                    </div>
                    <div style="padding:6px 10px;border-radius:9999px;background:#eef2ff;color:#3730a3;font-size:12px;font-weight:700;">%s</div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
                    <div style="padding:12px;border-radius:14px;background:#ffffff;border:1px solid #e5e7eb;">
                        <div style="font-size:12px;color:#64748b;">%s</div>
                        <div style="margin-top:4px;font-weight:600;color:#111827;">%s</div>
                    </div>
                    <div style="padding:12px;border-radius:14px;background:#ffffff;border:1px solid #e5e7eb;">
                        <div style="font-size:12px;color:#64748b;">%s</div>
                        <div style="margin-top:4px;font-weight:600;color:#111827;">%s</div>
                    </div>
                </div>
                <div style="font-size:13px;color:#475569;">%s</div>
            </div>',
            e(__('Ready-made field for ad form')),
            e($record->name),
            e($record->type->label()),
            e(__('Category')),
            e($record->category !== null ? $record->category->full_name : '—'),
            e(__('Possible values')),
            e($options),
            e($record->description ?? __('Users will simply fill in this prepared field when creating an ad.')),
        ));
    }
}
