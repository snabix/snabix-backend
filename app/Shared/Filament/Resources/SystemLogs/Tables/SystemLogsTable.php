<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs\Tables;

use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SystemLogsTable
{
    public static function configure(Table $table, bool $onlyErrors = false): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('level')
                    ->label('Уровень')
                    ->badge()
                    ->formatStateUsing(fn(EloquentSystemLog $record): string => $record->level->label())
                    ->color(fn(EloquentSystemLog $record): string => $record->level->color())
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('action')
                    ->label('Действие')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('message')
                    ->label('Сообщение')
                    ->searchable()
                    ->wrap()
                    ->limit(90)
                    ->description(fn(EloquentSystemLog $record): string => $record->short_context ?? 'Без дополнительных деталей'),

                TextColumn::make('method')
                    ->label('Метод')
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status_code')
                    ->label('HTTP')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('duration_ms')
                    ->label('Время, мс')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Пользователь')
                    ->placeholder('system')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Уровень')
                    ->options([
                        'info'     => 'Информация',
                        'warning'  => 'Предупреждение',
                        'error'    => 'Ошибка',
                        'critical' => 'Критическая ошибка',
                    ]),
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(self::categoryOptions()),
                SelectFilter::make('method')
                    ->label('Метод')
                    ->options([
                        'GET'    => 'GET',
                        'POST'   => 'POST',
                        'PUT'    => 'PUT',
                        'PATCH'  => 'PATCH',
                        'DELETE' => 'DELETE',
                    ]),
                SelectFilter::make('status_code')
                    ->label('HTTP-статус')
                    ->options(self::statusCodeOptions()),
                Filter::make('created_at')
                    ->label('Период')
                    ->schema([
                        DatePicker::make('from')->label('С'),
                        DatePicker::make('until')->label('По'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from  = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if (is_string($from) && $from !== '') {
                            $query->whereDate('created_at', '>=', $from);
                        }

                        if (is_string($until) && $until !== '') {
                            $query->whereDate('created_at', '<=', $until);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Открыть'),
                ]),
            ])
            ->emptyStateHeading($onlyErrors ? 'Ошибок пока нет' : 'Журнал пока пуст')
            ->emptyStateDescription(
                $onlyErrors
                    ? 'Здесь будут отображаться ошибки, предупреждения и исключения системы.'
                    : 'Здесь будут отображаться действия пользователей, HTTP-запросы и системные события.',
            );
    }

    /**
     * @return array<string, string>
     */
    private static function categoryOptions(): array
    {
        $options = EloquentSystemLog::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category', 'category')
            ->all();

        /** @var array<string, string> $options */
        return $options;
    }

    /**
     * @return array<int, string>
     */
    private static function statusCodeOptions(): array
    {
        $rawOptions = EloquentSystemLog::query()
            ->whereNotNull('status_code')
            ->select('status_code')
            ->distinct()
            ->orderBy('status_code')
            ->pluck('status_code', 'status_code')
            ->all();

        $options    = [];

        foreach ($rawOptions as $value) {
            if (! is_int($value) && ! (is_string($value) && is_numeric($value))) {
                continue;
            }

            $statusCode           = (int) $value;
            $options[$statusCode] = (string) $statusCode;
        }

        return $options;
    }
}
