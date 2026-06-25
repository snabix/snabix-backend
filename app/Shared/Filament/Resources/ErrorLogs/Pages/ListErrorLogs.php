<?php

declare(strict_types=1);

namespace App\Shared\Filament\Resources\ErrorLogs\Pages;

use App\Shared\Domain\Enums\SystemLogLevel;
use App\Shared\Filament\Resources\ErrorLogs\ErrorLogResource;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListErrorLogs extends ListRecords
{
    protected static string $resource = ErrorLogResource::class;

    public function getTitle(): string
    {
        return 'Ошибки и предупреждения';
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearErrorLogs')
                ->label('Очистить ошибки')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Очистить ошибки и предупреждения?')
                ->modalDescription('Будут удалены записи с уровнями warning, error и critical. Информационные записи журнала действий останутся.')
                ->modalSubmitActionLabel('Очистить')
                ->successNotificationTitle('Ошибки и предупреждения очищены')
                ->action(function (): void {
                    EloquentSystemLog::query()
                        ->whereIn('level', [
                            SystemLogLevel::WARNING->value,
                            SystemLogLevel::ERROR->value,
                            SystemLogLevel::CRITICAL->value,
                        ])
                        ->delete();

                    $this->resetTable();
                }),
        ];
    }
}
