<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Schemas;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Category structure'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Category name')
                            ->translateLabel(),

                        TextEntry::make('parentCategory.name')
                            ->label('Parent category')
                            ->translateLabel()
                            ->placeholder(__('Root category')),

                        TextEntry::make('slug')
                            ->translateLabel()
                            ->copyable(),

                        ImageEntry::make('iconMedia')
                            ->label('Icon')
                            ->translateLabel()
                            ->state(fn(EloquentCategory $record): ?string => $record->iconMedia?->getFullUrl())
                            ->circular(),

                        TextEntry::make('sort_order')
                            ->label('Sort order')
                            ->translateLabel(),

                        TextEntry::make('path')
                            ->label('Current path')
                            ->translateLabel()
                            ->placeholder('-'),

                        TextEntry::make('depth')
                            ->label('Level')
                            ->translateLabel(),

                        IconEntry::make('is_active')
                            ->label('Category is active')
                            ->translateLabel()
                            ->boolean(),

                        TextEntry::make('children_count')
                            ->label('Child categories count')
                            ->translateLabel()
                            ->state(fn(EloquentCategory $record): int => $record->children()->count()),

                        TextEntry::make('description')
                            ->translateLabel()
                            ->placeholder(__('Description has not been filled in yet.'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
