<?php

declare(strict_types=1);

namespace App\Shared\Domain\Enums;

enum SystemLogLevel: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::INFO => 'Информация',
            self::WARNING => 'Предупреждение',
            self::ERROR => 'Ошибка',
            self::CRITICAL => 'Критическая ошибка',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INFO => 'info',
            self::WARNING => 'warning',
            self::ERROR => 'danger',
            self::CRITICAL => 'danger',
        };
    }
}
