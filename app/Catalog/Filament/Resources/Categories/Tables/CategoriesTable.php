<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Tables;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with(['parentCategory', 'iconMedia'])->withCount('children'))
            ->defaultSort('path')
            ->recordUrl(fn(EloquentCategory $record): string => CategoryResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ImageColumn::make('iconMedia')
                    ->label('Icon')
                    ->translateLabel()
                    ->getStateUsing(fn(EloquentCategory $record): ?string => $record->iconMedia?->getFullUrl())
                    ->circular()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Category tree')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(fn(string $state, EloquentCategory $record): HtmlString => self::renderTreeLabel($state, $record))
                    ->description(
                        fn(EloquentCategory $record): string => $record->parentCategory?->name !== null
                            ? __('Parent') . ': ' . $record->parentCategory->name
                            : __('Root category'),
                    ),

                TextColumn::make('full_name')
                    ->label('Full path')
                    ->translateLabel()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('parentCategory.name')
                    ->label('Parent')
                    ->translateLabel()
                    ->placeholder(__('Root'))
                    ->toggleable(),

                TextColumn::make('children_count')
                    ->label('Children')
                    ->translateLabel()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function (int $state): string {
                        $formatted = Number::format($state, locale: 'ru');

                        return is_string($formatted) ? $formatted : (string) $state;
                    })
                    ->sortable(),

                TextColumn::make('depth')
                    ->label('Level')
                    ->translateLabel()
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->translateLabel()
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? __('Active') : __('Hidden'))
                    ->color(fn(bool $state): string => $state ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('slug')
                    ->translateLabel()
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Sort order')
                    ->translateLabel()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('root_categories')
                    ->translateLabel()
                    ->placeholder(__('All categories'))
                    ->trueLabel(__('Root categories only'))
                    ->falseLabel(__('Child categories only'))
                    ->queries(
                        true: fn(Builder $query): Builder => $query->whereNull('parent_id'),
                        false: fn(Builder $query): Builder => $query->whereNotNull('parent_id'),
                        blank: fn(Builder $query): Builder => $query,
                    ),
                SelectFilter::make('is_active')
                    ->translateLabel()
                    ->options([
                        '1' => __('Active only'),
                        '0' => __('Hidden only'),
                    ]),
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->translateLabel()
                    ->options(fn(): array => app(CategoryRepositoryInterface::class)->parentOptions()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->translateLabel(),
                    EditAction::make()
                        ->translateLabel(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Delete selected')
                    ->translateLabel(),
            ])
            ->emptyStateHeading(__('Categories have not been created yet'))
            ->emptyStateDescription(__('Create the first root section or prepare an import from a public source.'));
    }

    private static function renderTreeLabel(
        string $state,
        EloquentCategory $record,
    ): HtmlString {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', max($record->depth, 0));
        $accent = $record->depth > 0
            ? '<span style="display:inline-flex; width:24px; color:#9ca3af; font-weight:700;">└</span>'
            : '<span style="display:inline-flex; width:24px; color:#ec4899; font-weight:700;">●</span>';

        return new HtmlString(sprintf(
            '<div style="display:flex; align-items:center; gap:6px;">%s%s<span style="font-weight:700;">%s</span></div>',
            $indent,
            $accent,
            e($state),
        ));
    }
}
