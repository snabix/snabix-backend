<?php

declare(strict_types=1);

namespace App\Listing\Domain\Enums;

enum ListingType: int
{
    case PRODUCT = 1;
    case SERVICE = 2;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT => 'Товар',
            self::SERVICE => 'Услуга',
        };
    }
}
