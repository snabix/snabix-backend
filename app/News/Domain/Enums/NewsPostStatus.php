<?php

declare(strict_types=1);

namespace App\News\Domain\Enums;

enum NewsPostStatus: int
{
    case DRAFT     = 1;
    case PUBLISHED = 2;
    case ARCHIVED  = 3;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $status): array => [$status->value => $status->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Черновик',
            self::PUBLISHED => 'Опубликовано',
            self::ARCHIVED  => 'Архив',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::DRAFT     => 'draft',
            self::PUBLISHED => 'published',
            self::ARCHIVED  => 'archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT     => 'gray',
            self::PUBLISHED => 'success',
            self::ARCHIVED  => 'warning',
        };
    }
}
