<?php

declare(strict_types=1);

namespace App\Listing\Domain\Enums;

enum ListingStatus: int
{
    case DRAFT          = 1;
    case PENDING_REVIEW = 2;
    case PUBLISHED      = 3;
    case REJECTED       = 4;
    case ARCHIVED       = 5;

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $status): array => [$status->value => $status->label()])
            ->all();
    }

    public static function fromApiName(string $name): ?self
    {
        return match ($name) {
            'draft'         => self::DRAFT,
            'pendingReview' => self::PENDING_REVIEW,
            'published'     => self::PUBLISHED,
            'rejected'      => self::REJECTED,
            'archived'      => self::ARCHIVED,
            default         => null,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT          => 'Черновик',
            self::PENDING_REVIEW => 'На проверке',
            self::PUBLISHED      => 'Опубликовано',
            self::REJECTED       => 'Отклонено',
            self::ARCHIVED       => 'В архиве',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::DRAFT          => 'draft',
            self::PENDING_REVIEW => 'pendingReview',
            self::PUBLISHED      => 'published',
            self::REJECTED       => 'rejected',
            self::ARCHIVED       => 'archived',
        };
    }
}
