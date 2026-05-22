<?php

declare(strict_types=1);

namespace App\Listing\Filament\Widgets;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Infrastructure\Models\EloquentListing;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListingModerationStatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    protected function getStats(): array
    {
        $pendingCount   = $this->countByStatus(ListingStatus::PENDING_REVIEW);
        $publishedCount = $this->countByStatus(ListingStatus::PUBLISHED);
        $rejectedCount  = $this->countByStatus(ListingStatus::REJECTED);
        $draftCount     = $this->countByStatus(ListingStatus::DRAFT);

        return [
            Stat::make('На проверке', (string) $pendingCount)
                ->description('Требуют решения модератора')
                ->color($pendingCount > 0 ? 'warning' : 'success'),
            Stat::make('Опубликовано', (string) $publishedCount)
                ->description('Активные объявления на витрине')
                ->color('success'),
            Stat::make('Отклонено', (string) $rejectedCount)
                ->description('Нужно проверить причины отказа')
                ->color($rejectedCount > 0 ? 'danger' : 'gray'),
            Stat::make('Черновики', (string) $draftCount)
                ->description('Не отправлены пользователями на проверку')
                ->color('gray'),
        ];
    }

    private function countByStatus(ListingStatus $status): int
    {
        return EloquentListing::query()
            ->where('status', $status->value)
            ->count();
    }
}
