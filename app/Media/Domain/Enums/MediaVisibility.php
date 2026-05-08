<?php

declare(strict_types=1);

namespace App\Media\Domain\Enums;

enum MediaVisibility: int
{
    case PUBLIC = 1;
    case PRIVATE = 2;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $visibility): array => [$visibility->value => $visibility->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Публичный',
            self::PRIVATE => 'Приватный',
        };
    }

    public function disk(): string
    {
        return match ($this) {
            self::PUBLIC => 'public',
            self::PRIVATE => 'local',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLIC => 'success',
            self::PRIVATE => 'gray',
        };
    }
}
