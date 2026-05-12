<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Schemas;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('Category structure'))
                    ->description(__('Create a category tree with any nesting depth. Path and depth are calculated automatically.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Category name')
                            ->translateLabel()
                            ->placeholder(__('For example, Electronics'))
                            ->prefixIcon(Heroicon::OutlinedTag)
                            ->autofocus()
                            ->required()
                            ->maxLength(255),

                        Select::make('parent_id')
                            ->label('Parent category')
                            ->translateLabel()
                            ->placeholder(__('Root category'))
                            ->options(fn(?EloquentCategory $record): array => app(CategoryRepositoryInterface::class)->parentOptions($record?->id))
                            ->searchable()
                            ->preload()
                            ->native(false),

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

                        TextInput::make('path')
                            ->label('Current path')
                            ->translateLabel()
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn(?string $state): string => $state ?? __('Will be calculated after saving')),
                    ]),

                Section::make(__('Description'))
                    ->description(__('A short note for the admin panel and the future frontend catalog.'))
                    ->schema([
                        Textarea::make('description')
                            ->translateLabel()
                            ->placeholder(__('Describe the purpose of the category, its specifics, or content rules.'))
                            ->rows(4)
                            ->maxLength(2000),
                    ]),

                Section::make(__('Child categories'))
                    ->description(__('Shows direct child categories of the current section.'))
                    ->visible(fn (?EloquentCategory $record): bool => $record !== null)
                    ->schema([
                        Placeholder::make('child_categories_preview')
                            ->label('Child categories')
                            ->translateLabel()
                            ->content(fn (?EloquentCategory $record): HtmlString => self::renderChildCategories($record)),
                    ]),
            ]);
    }

    private static function renderChildCategories(?EloquentCategory $record): HtmlString
    {
        if ($record === null) {
            return new HtmlString('');
        }

        $children = $record->children()->get();

        if ($children->isEmpty()) {
            return new HtmlString('<span style="color:#6b7280;">' . e(__('This category has no children yet.')) . '</span>');
        }

        $items = $children
            ->map(fn (EloquentCategory $child): string => sprintf(
                '<a href="%s" style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:16px;background:#ffffff;text-decoration:none;color:#111827;">
                    <span style="font-weight:700;">%s</span>
                    <span style="font-size:12px;color:#6b7280;">%s</span>
                </a>',
                e(CategoryResource::getUrl('edit', ['record' => $child])),
                e($child->name),
                e($child->slug),
            ))
            ->implode('');

        return new HtmlString(sprintf(
            '<div style="display:grid;gap:10px;">%s</div>',
            $items,
        ));
    }
}
