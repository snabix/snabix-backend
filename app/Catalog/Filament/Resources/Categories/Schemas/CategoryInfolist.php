<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Schemas;

use App\Catalog\Filament\Resources\Categories\CategoryResource;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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

                Section::make(__('Child categories'))
                    ->description(__('Shows direct child categories of the current section.'))
                    ->schema([
                        TextEntry::make('child_categories')
                            ->label('Child categories')
                            ->translateLabel()
                            ->state(fn(EloquentCategory $record): HtmlString => self::renderChildCategories($record))
                            ->html(),
                    ]),
            ]);
    }

    private static function renderChildCategories(EloquentCategory $record): HtmlString
    {
        $children = $record->children()->get();

        if ($children->isEmpty()) {
            return new HtmlString('<span style="color:#6b7280;">' . e(__('This category has no children yet.')) . '</span>');
        }

        $items    = $children
            ->map(fn(EloquentCategory $child): string => sprintf(
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
