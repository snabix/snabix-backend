<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Services;

class FrontendUrlBuilder
{
    /**
     * @param array<string, scalar|null> $query
     */
    public function build(string $baseUrl, array $query = []): string
    {
        $normalizedBaseUrl = rtrim($baseUrl, '/');
        $filteredQuery     = array_filter(
            $query,
            static fn(mixed $value): bool => $value !== null && $value !== '',
        );

        if ($filteredQuery === []) {
            return $normalizedBaseUrl;
        }

        $separator         = str_contains($normalizedBaseUrl, '?') ? '&' : '?';

        return $normalizedBaseUrl . $separator . http_build_query($filteredQuery);
    }
}
