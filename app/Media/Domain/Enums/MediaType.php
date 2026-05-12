<?php

declare(strict_types=1);

namespace App\Media\Domain\Enums;

enum MediaType: int
{
    case IMAGE    = 1;
    case DOCUMENT = 2;
    case VIDEO    = 3;
    case FILE     = 4;

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
            self::IMAGE    => 'Изображение',
            self::DOCUMENT => 'Документ',
            self::VIDEO    => 'Видео',
            self::FILE     => 'Файл',
        };
    }

    public function directory(): string
    {
        return match ($this) {
            self::IMAGE    => 'images',
            self::DOCUMENT => 'documents',
            self::VIDEO    => 'videos',
            self::FILE     => 'files',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IMAGE    => 'success',
            self::DOCUMENT => 'info',
            self::VIDEO    => 'warning',
            self::FILE     => 'gray',
        };
    }
}
