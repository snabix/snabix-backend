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

    public static function fromApiName(string $name): ?self
    {
        return match ($name) {
            'new'           => self::NEW,
            'used'          => self::USED,
            'notApplicable' => self::NOT_APPLICABLE,
            default         => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW            => 'Новый',
            self::USED           => 'Б/у',
            self::NOT_APPLICABLE => 'Не применяется',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::NEW            => 'new',
            self::USED           => 'used',
            self::NOT_APPLICABLE => 'notApplicable',
        };
    }
}
