<?php

declare(strict_types=1);

namespace App\Review\Domain\Enums;

enum UserReviewStatus: string
{
    case PUBLISHED = 'published';
    case HIDDEN    = 'hidden';
    case REJECTED  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PUBLISHED => 'Опубликован',
            self::HIDDEN    => 'Скрыт',
            self::REJECTED  => 'Отклонен',
        };
    }
}
