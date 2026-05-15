<?php

declare(strict_types=1);

namespace App\Listing\Domain\Enums;

enum ListingCondition: int
{
    case NEW            = 1;
    case USED           = 2;
    case NOT_APPLICABLE = 3;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $condition): array => [$condition->value => $condition->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW            => 'Новый',
            self::USED           => 'Б/у',
            self::NOT_APPLICABLE => 'Не применяется',
        };
    }
}
