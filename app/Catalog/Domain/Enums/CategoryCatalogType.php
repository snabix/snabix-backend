<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enums;

enum CategoryCatalogType: int
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
            self::PRODUCT => 'Товары',
            self::SERVICE => 'Услуги',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::PRODUCT => 'product',
            self::SERVICE => 'service',
        };
    }
}
