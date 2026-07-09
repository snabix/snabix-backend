<?php

declare(strict_types=1);

namespace App\Listing\Filament\Resources\Listings\Actions;

use App\Listing\Application\Services\ListingModerationService;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Services\ListingStatusTransitionPolicy;
use App\Listing\Infrastructure\Models\EloquentListing;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class ListingModerationActions
{
    /**
     * @return list<Action>
     */
    public static function make(): array
    {
        return [
            self::statusAction('publishListing', ListingStatus::PUBLISHED, 'Опубликовать', 'success'),
            self::statusAction('rejectListing', ListingStatus::REJECTED, 'Отклонить', 'danger'),
            self::statusAction('archiveListing', ListingStatus::ARCHIVED, 'В архив', 'gray'),
        ];
    }

    private static function statusAction(
        string $name,
        ListingStatus $targetStatus,
        string $label,
        string $color,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->color($color)
            ->icon(self::icon($targetStatus))
            ->visible(fn(EloquentListing $record): bool => self::canRun($record, $targetStatus))
            ->requiresConfirmation()
            ->modalHeading($label)
            ->modalDescription(fn(EloquentListing $record): string => sprintf(
                'Статус объявления "%s" будет изменен: %s -> %s.',
                $record->title,
                $record->status->label(),
                $targetStatus->label(),
            ))
            ->schema([
                Textarea::make('message')
                    ->label('Сообщение пользователю')
                    ->placeholder(self::placeholder($targetStatus))
                    ->helperText('Сообщение попадет в уведомление на сайте и email. Для отклонения причина обязательна.')
                    ->maxLength(1000)
                    ->rows(4)
                    ->required($targetStatus === ListingStatus::REJECTED),
            ])
            ->modalSubmitActionLabel($label)
            ->action(function (EloquentListing $record, array $data) use ($targetStatus): void {
                $adminId = Auth::guard('admin')->id();

                app(ListingModerationService::class)->moderate(
                    listing: $record,
                    targetStatus: $targetStatus,
                    message: is_string($data['message'] ?? null) ? $data['message'] : null,
                    adminId: is_string($adminId) || is_int($adminId) ? (string) $adminId : null,
                );

                Notification::make()
                    ->title('Статус объявления обновлен')
                    ->success()
                    ->send();
            });
    }

    private static function canRun(EloquentListing $record, ListingStatus $targetStatus): bool
    {
        if ($record->status === $targetStatus || ! Gate::allows('moderate', $record)) {
            return false;
        }

        return app(ListingStatusTransitionPolicy::class)->canTransition($record->status, $targetStatus);
    }

    private static function icon(ListingStatus $targetStatus): string
    {
        return match ($targetStatus) {
            ListingStatus::PUBLISHED => 'heroicon-o-check-circle',
            ListingStatus::REJECTED  => 'heroicon-o-x-circle',
            ListingStatus::ARCHIVED  => 'heroicon-o-archive-box',
            default                  => 'heroicon-o-shield-check',
        };
    }

    private static function placeholder(ListingStatus $targetStatus): string
    {
        return match ($targetStatus) {
            ListingStatus::PUBLISHED => 'Например: Объявление опубликовано и уже доступно покупателям.',
            ListingStatus::REJECTED  => 'Например: Добавьте реальные фотографии товара и уточните описание.',
            ListingStatus::ARCHIVED  => 'Например: Объявление перенесено в архив по решению модерации.',
            default                  => 'Добавьте короткий комментарий для пользователя.',
        };
    }
}
