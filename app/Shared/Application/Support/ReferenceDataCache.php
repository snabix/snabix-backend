<?php

declare(strict_types=1);

namespace App\Shared\Application\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

final readonly class ReferenceDataCache
{
    private const int TTL_SECONDS             = 3600;

    private const string CATALOG_VERSION_KEY  = 'reference-data:catalog:version';

    private const string LOCATION_VERSION_KEY = 'reference-data:location:version';

    /**
     * @template TValue
     *
     * @param  Closure(): TValue $callback
     * @return TValue
     */
    public function rememberCatalog(string $key, Closure $callback): mixed
    {
        return Cache::remember(
            $this->versionedKey(self::CATALOG_VERSION_KEY, $key),
            self::TTL_SECONDS,
            $callback,
        );
    }

    /**
     * @template TValue
     *
     * @param  Closure(): TValue $callback
     * @return TValue
     */
    public function rememberLocation(string $key, Closure $callback): mixed
    {
        return Cache::remember(
            $this->versionedKey(self::LOCATION_VERSION_KEY, $key),
            self::TTL_SECONDS,
            $callback,
        );
    }

    public function invalidateCatalog(): void
    {
        $this->bumpVersion(self::CATALOG_VERSION_KEY);
    }

    public function invalidateLocation(): void
    {
        $this->bumpVersion(self::LOCATION_VERSION_KEY);
    }

    private function versionedKey(string $versionKey, string $key): string
    {
        return $key . ':v' . $this->version($versionKey);
    }

    private function version(string $versionKey): int
    {
        $version = Cache::get($versionKey);

        if (is_int($version) || (is_string($version) && ctype_digit($version))) {
            return (int) $version;
        }

        Cache::forever($versionKey, 1);

        return 1;
    }

    private function bumpVersion(string $versionKey): void
    {
        Cache::forever($versionKey, $this->version($versionKey) + 1);
    }
}
