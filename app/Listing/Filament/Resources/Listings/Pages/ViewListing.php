<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Pages;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Filament\Resources\Listings\ListingResource;
use App\Listing\Infrastructure\Models\EloquentListing;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewListing extends ViewRecord
{
    protected static string $resource = ListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                $this->statusPreviewAction('publish', ListingStatus::PUBLISHED, 'Опубликовать', 'success'),
                $this->statusPreviewAction('reject', ListingStatus::REJECTED, 'Отклонить', 'danger'),
                $this->statusPreviewAction('archive', ListingStatus::ARCHIVED, 'В архив', 'gray'),
            ])
                ->label('Сменить статус')
                ->icon('heroicon-o-shield-check')
                ->button(),
            EditAction::make()
                ->label('Корректировать'),
        ];
    }

    private function statusPreviewAction(
        string $name,
        ListingStatus $targetStatus,
        string $label,
        string $color,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->color($color)
            ->requiresConfirmation()
            ->modalHeading($label)
            ->modalDescription(fn(): string => sprintf(
                'Кнопка уже добавлена в интерфейс, но статус пока не меняется. Следующий шаг: подключить transition %s -> %s, проверку прав, причину отказа и audit log.',
                $this->currentListing()->status->label(),
                $targetStatus->label(),
            ))
            ->modalSubmitActionLabel('Понятно')
            ->action(fn(): null => null);
    }

    private function currentListing(): EloquentListing
    {
        /** @var EloquentListing $record */
        $record = $this->getRecord();

        return $record;
    }
}
