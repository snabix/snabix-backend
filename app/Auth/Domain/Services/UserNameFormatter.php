<?php

declare(strict_types=1);

namespace App\Auth\Domain\Services;

final class UserNameFormatter
{
    public static function fullName(?string $firstName, ?string $lastName): ?string
    {
        $parts = [];

        foreach ([$firstName, $lastName] as $part) {
            if ($part !== null && trim($part) !== '') {
                $parts[] = trim($part);
            }
        }

        return $parts === [] ? null : implode(' ', $parts);
    }

    public static function accountLabel(
        ?string $firstName,
        ?string $lastName,
        string $email,
    ): string {
        return self::fullName($firstName, $lastName) ?? $email;
    }
}
