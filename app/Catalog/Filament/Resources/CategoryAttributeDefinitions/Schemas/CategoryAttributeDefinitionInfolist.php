<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Schemas;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryAttributeDefinitionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Attribute overview'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('category.full_name')
                            ->label('Category')
                            ->translateLabel(),
                        TextEntry::make('type')
                            ->label('Attribute type')
                            ->translateLabel()
                            ->formatStateUsing(fn (EloquentCategoryAttributeDefinition $record): string => $record->type->label()),
                        TextEntry::make('name')
                            ->translateLabel(),
                        TextEntry::make('slug')
                            ->translateLabel(),
                        TextEntry::make('unit')
                            ->translateLabel()
                            ->placeholder('—'),
                        TextEntry::make('group_name')
                            ->label('Attribute group')
                            ->translateLabel()
                            ->placeholder('—'),
                        TextEntry::make('sort_order')
                            ->label('Sort order')
                            ->translateLabel(),
                        TextEntry::make('schema_version')
                            ->label('Schema version')
                            ->translateLabel(),
                    ]),

                Section::make(__('Behavior in listing forms'))
                    ->columns(2)
                    ->schema([
                        IconEntry::make('is_required')
                            ->label('Attribute is required')
                            ->translateLabel()
                            ->boolean(),
                        IconEntry::make('is_filterable')
                            ->label('Attribute is filterable')
                            ->translateLabel()
                            ->boolean(),
                        IconEntry::make('show_in_card')
                            ->label('Show in listing card')
                            ->translateLabel()
                            ->boolean(),
                        IconEntry::make('applies_to_children')
                            ->label('Apply to child categories')
                            ->translateLabel()
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Attribute is active')
                            ->translateLabel()
                            ->boolean(),
                    ]),

                Section::make(__('Description and options'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->translateLabel()
                            ->placeholder(__('Description has not been filled in yet.')),
                        TextEntry::make('placeholder')
                            ->label('Field placeholder')
                            ->translateLabel()
                            ->placeholder('—'),
                        TextEntry::make('help_text')
                            ->label('Field help text')
                            ->translateLabel()
                            ->placeholder('—'),
                        KeyValueEntry::make('options')
                            ->label('Attribute options')
                            ->translateLabel()
                            ->formatStateUsing(function (?array $state): array {
                                if (! is_array($state)) {
                                    return [];
                                }

                                $items = [];

                                foreach ($state as $index => $value) {
                                    $items[(string) ($index + 1)] = (string) $value;
                                }

                                return $items;
                            }),
                        KeyValueEntry::make('default_value')
                            ->label('Default value')
                            ->translateLabel(),
                        KeyValueEntry::make('dependency_rules')
                            ->label('Dependency rules')
                            ->translateLabel(),
                    ]),
            ]);
    }
}
