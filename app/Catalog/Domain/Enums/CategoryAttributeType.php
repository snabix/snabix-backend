<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Enums;

enum CategoryAttributeType: int
{
    case TEXT        = 1;
    case NUMBER      = 2;
    case BOOLEAN     = 3;
    case SELECT      = 4;
    case MULTISELECT = 5;
    case DATE        = 6;

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
            self::TEXT        => 'Текст',
            self::NUMBER      => 'Число',
            self::BOOLEAN     => 'Да/Нет',
            self::SELECT      => 'Выбор одного значения',
            self::MULTISELECT => 'Выбор нескольких значений',
            self::DATE        => 'Дата',
        };
    }

    public function apiName(): string
    {
        return match ($this) {
            self::TEXT        => 'text',
            self::NUMBER      => 'number',
            self::BOOLEAN     => 'boolean',
            self::SELECT      => 'select',
            self::MULTISELECT => 'multiSelect',
            self::DATE        => 'date',
        };
    }
}
