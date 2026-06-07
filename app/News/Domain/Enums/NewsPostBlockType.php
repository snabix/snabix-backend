<?php

declare(strict_types=1);

namespace App\News\Domain\Enums;

enum NewsPostBlockType: int
{
    case LEAD       = 1;
    case PARAGRAPH  = 2;
    case QUOTE      = 3;
    case SPLIT      = 4;
    case STEPS      = 5;
    case METRICS    = 6;
    case IMAGE      = 7;
    case GALLERY    = 8;
    case TABLE      = 9;
    case IMAGE_GRID = 10;
    case CTA        = 11;

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
            self::LEAD       => 'Лид',
            self::PARAGRAPH  => 'Текст',
            self::QUOTE      => 'Цитата',
            self::SPLIT      => 'Сетка тезисов',
            self::STEPS      => 'Шаги',
            self::METRICS    => 'Метрики',
            self::IMAGE      => 'Изображение',
            self::GALLERY    => 'Галерея',
            self::TABLE      => 'Таблица',
            self::IMAGE_GRID => 'Изображение + текст',
            self::CTA        => 'Призыв к действию',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::LEAD       => 'lead',
            self::PARAGRAPH  => 'paragraph',
            self::QUOTE      => 'quote',
            self::SPLIT      => 'split',
            self::STEPS      => 'steps',
            self::METRICS    => 'metrics',
            self::IMAGE      => 'image',
            self::GALLERY    => 'gallery',
            self::TABLE      => 'table',
            self::IMAGE_GRID => 'imageGrid',
            self::CTA        => 'cta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LEAD, self::PARAGRAPH, self::QUOTE     => 'info',
            self::IMAGE, self::GALLERY, self::IMAGE_GRID => 'success',
            self::TABLE, self::METRICS                   => 'warning',
            self::SPLIT, self::STEPS, self::CTA          => 'primary',
        };
    }
}
