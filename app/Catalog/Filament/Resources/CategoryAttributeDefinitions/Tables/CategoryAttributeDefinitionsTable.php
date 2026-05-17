<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\CategoryAttributeDefinitions\Tables;

use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Filament\Resources\CategoryAttributeDefinitions\CategoryAttributeDefinitionResource;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class CategoryAttributeDefinitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with('category'))
            ->defaultSort('sort_order')
            ->recordUrl(fn(EloquentCategoryAttributeDefinition $record): string => CategoryAttributeDefinitionResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Attribute')
                    ->translateLabel()
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(fn(string $state, EloquentCategoryAttributeDefinition $record): HtmlString => new HtmlString(sprintf(
                        '<div style="display:grid;gap:4px;">
                            <span style="font-weight:700;color:#0f172a;">%s</span>
                            <span style="font-size:12px;color:#64748b;">%s</span>
                        </div>',
                        e($state),
                        e($record->slug),
                    ))),

                TextColumn::make('category.full_name')
                    ->label('Category')
                    ->translateLabel()
                    ->wrap()
                    ->searchable(),

                TextColumn::make('group_name')
                    ->label('Group')
                    ->translateLabel()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Attribute type')
                    ->translateLabel()
                    ->badge()
                    ->formatStateUsing(fn(EloquentCategoryAttributeDefinition $record): string => $record->type->label())
                    ->color(fn(EloquentCategoryAttributeDefinition $record): string => match ($record->type) {
                        CategoryAttributeType::TEXT        => 'gray',
                        CategoryAttributeType::NUMBER      => 'info',
                        CategoryAttributeType::BOOLEAN     => 'success',
                        CategoryAttributeType::SELECT      => 'primary',
                        CategoryAttributeType::MULTISELECT => 'warning',
                        CategoryAttributeType::DATE        => 'danger',
                    }),

                TextColumn::make('options')
                    ->label('Attribute options')
                    ->translateLabel()
                    ->toggleable()
                    ->formatStateUsing(fn(?array $state): string => is_array($state) && $state !== [] ? implode(', ', array_map(static function (mixed $item): string {
                        if (is_scalar($item)) {
                            return (string) $item;
                        }

                        return json_encode($item, JSON_UNESCAPED_UNICODE) ?: '';
                    }, $state)) : '—')
                    ->wrap(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->translateLabel()
                    ->boolean(),

                IconColumn::make('is_filterable')
                    ->label('Filterable')
                    ->translateLabel()
                    ->boolean(),

                IconColumn::make('show_in_card')
                    ->label('Card')
                    ->translateLabel()
                    ->boolean(),

                IconColumn::make('applies_to_children')
                    ->label('For children')
                    ->translateLabel()
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->translateLabel()
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Sort order')
                    ->translateLabel()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->translateLabel()
                    ->options(fn(): array => EloquentCategory::query()->orderBy('path')->get()->mapWithKeys(fn(EloquentCategory $category): array => [$category->id => $category->full_name])->all())
                    ->searchable(),
                SelectFilter::make('type')
                    ->label('Attribute type')
                    ->translateLabel()
                    ->options(CategoryAttributeType::options()),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->translateLabel()
                    ->options([
                        '1' => __('Active only'),
                        '0' => __('Hidden only'),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->translateLabel(),
                    EditAction::make()->translateLabel(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Delete selected')
                    ->translateLabel(),
            ])
            ->emptyStateHeading(__('Category attributes have not been created yet'))
            ->emptyStateDescription(__('Create ready-made fields for categories so users only fill in the prepared ad form.'));
    }
}
