<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Support;

final readonly class FrontendDomainConfig
{
    /**
     * @param  list<string> $fallbackUrls
     * @return list<string>
     */
    public static function urls(?string $primaryUrl, ?string $additionalUrls, array $fallbackUrls = []): array
    {
        return self::uniqueList([
            $primaryUrl,
            ...self::csv($additionalUrls),
            ...$fallbackUrls,
        ]);
    }

    /**
     * @return list<string>
     */
    public static function statefulDomains(?string $domains, string $defaultDomains): array
    {
        return self::uniqueList(self::csv($domains ?? $defaultDomains));
    }

    /**
     * @return list<string>
     */
    private static function csv(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return array_map(
            static fn(string $item): string => trim($item),
            explode(',', $value),
        );
    }

    /**
     * @param  list<string|null> $items
     * @return list<string>
     */
    private static function uniqueList(array $items): array
    {
        return array_values(array_unique(array_filter(
            $items,
            static fn(?string $item): bool => is_string($item) && trim($item) !== '',
        )));
    }
}
