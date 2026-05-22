<?php

declare(strict_types=1);

namespace App\Listing\Filament\Pages;

use App\Listing\Filament\Widgets\ListingModerationStatsWidget;
use App\Listing\Filament\Widgets\PendingListingsTableWidget;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ListingModerationDashboard extends Dashboard
{
    use HasPageShield;

    protected static string $routePath                                = '/listing-moderation';

    protected static string | BackedEnum | null $navigationIcon       = Heroicon::OutlinedShieldCheck;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ShieldCheck;

    protected static string | UnitEnum | null $navigationGroup        = 'Модерация';

    protected static ?string $navigationLabel                         = 'Модерация объявлений';

    protected static ?int $navigationSort                             = 1;

    public function getTitle(): string
    {
        return 'Модерация объявлений';
    }

    public function getWidgets(): array
    {
        return [
            ListingModerationStatsWidget::class,
            PendingListingsTableWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
