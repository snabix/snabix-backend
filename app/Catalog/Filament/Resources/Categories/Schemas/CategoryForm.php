<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Schemas;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Category structure'))
                    ->description(
                        __('Create a category tree with any nesting depth. Path and depth are calculated automatically.'),
                    )
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Category name')
                                    ->translateLabel()
                                    ->placeholder(__('For example, Electronics'))
                                    ->prefixIcon(Heroicon::OutlinedTag)
                                    ->autofocus()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->translateLabel()
                                    ->placeholder(__('Will be generated automatically if left empty'))
                                    ->prefixIcon(Heroicon::OutlinedLink)
                                    ->maxLength(255)
                                    ->helperText(__('Used in URLs and category path building.')),

                                TextInput::make('sort_order')
                                    ->label('Sort order')
                                    ->translateLabel()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText(__('Lower values place the category higher among siblings.')),

                                Toggle::make('is_active')
                                    ->label('Category is active')
                                    ->translateLabel()
                                    ->default(true)
                                    ->inline(false),

                                Select::make('parent_id')
                                    ->label('Parent category')
                                    ->translateLabel()
                                    ->placeholder(__('Root category'))
                                    ->options(
                                        fn(?EloquentCategory $record): array => app(
                                            CategoryRepositoryInterface::class,
                                        )->parentOptions($record?->id),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                TextInput::make('path')
                                    ->label('Current path')
                                    ->translateLabel()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(
                                        fn(?string $state): string => $state
                                            ?? __('Will be calculated after saving'),
                                    ),
                            ]),

                        Textarea::make('description')
                            ->translateLabel()
                            ->placeholder(
                                __('Describe the purpose of the category, its specifics, or content rules.'),
                            )
                            ->rows(4)
                            ->maxLength(2000),

                        SpatieMediaLibraryFileUpload::make('category_icon_upload')
                            ->label('Icon')
                            ->translateLabel()
                            ->collection('category_icons')
                            ->image()
                            ->downloadable()
                            ->openable()
                            ->maxFiles(1)
                            ->disk('public')
                            ->maxSize(3072)
                            ->helperText(__('Загрузить изображение весом до 3 МБ')),
                    ]),
            ]);
    }
}
