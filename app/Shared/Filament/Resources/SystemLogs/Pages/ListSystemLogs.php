<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\SystemLogs\Pages;

use App\Shared\Filament\Resources\SystemLogs\SystemLogResource;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSystemLogs extends ListRecords
{
    protected static string $resource = SystemLogResource::class;

    public function getTitle(): string
    {
        return 'Журнал действий системы';
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearSystemLogs')
                ->label('Очистить журнал')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Очистить журнал действий?')
                ->modalDescription('Будут удалены все записи журнала действий системы. Это действие нельзя отменить.')
                ->modalSubmitActionLabel('Очистить')
                ->successNotificationTitle('Журнал действий очищен')
                ->action(function (): void {
                    EloquentSystemLog::query()->delete();
                    $this->resetTable();
                }),
        ];
    }
}
