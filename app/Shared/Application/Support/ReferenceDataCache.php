<?php

declare(strict_types=1);

namespace App\Shared\Application\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

final class ReferenceDataCache
{
    private const int TTL_SECONDS             = 3600;

    private const string CATALOG_VERSION_KEY  = 'reference-data:catalog:version';

    private const string LOCATION_VERSION_KEY = 'reference-data:location:version';

    private int $catalogBatchDepth            = 0;

    private bool $catalogInvalidationPending  = false;

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
        if ($this->catalogBatchDepth > 0) {
            $this->catalogInvalidationPending = true;

            return;
        }

        $this->bumpVersion(self::CATALOG_VERSION_KEY);
    }

    /**
     * @template TValue
     *
     * @param  Closure(): TValue $callback
     * @return TValue
     */
    public function batchCatalogInvalidation(Closure $callback): mixed
    {
        $isOutermostBatch = $this->catalogBatchDepth === 0;
        $this->catalogBatchDepth++;

        try {
            $result = $callback();
        } catch (Throwable $exception) {
            $this->catalogBatchDepth--;

            if ($isOutermostBatch) {
                $this->catalogInvalidationPending = false;
            }

            throw $exception;
        }

        $this->catalogBatchDepth--;

        if ($isOutermostBatch && $this->catalogInvalidationPending) {
            $this->catalogInvalidationPending = false;
            $this->bumpVersion(self::CATALOG_VERSION_KEY);
        }

        return $result;
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
        $version            = $this->normalizeVersion(Cache::get($versionKey));

        if ($version !== null) {
            return $version;
        }

        $initializedVersion = Cache::lock($versionKey . ':lock', 10)->block(5, function () use ($versionKey): int {
            $version = $this->normalizeVersion(Cache::get($versionKey));

            if ($version !== null) {
                return $version;
            }

            Cache::forever($versionKey, 1);

            return 1;
        });

        if (! is_int($initializedVersion)) {
            throw new RuntimeException(sprintf('Unable to initialize reference data cache version [%s].', $versionKey));
        }

        return $initializedVersion;
    }

    private function bumpVersion(string $versionKey): void
    {
        Cache::lock($versionKey . ':lock', 10)->block(5, function () use ($versionKey): void {
            if ($this->normalizeVersion(Cache::get($versionKey)) === null) {
                Cache::forever($versionKey, 1);
            }

            $version = Cache::increment($versionKey);

            if (! is_int($version)) {
                throw new RuntimeException(sprintf('Unable to increment reference data cache version [%s].', $versionKey));
            }
        });
    }

    private function normalizeVersion(mixed $version): ?int
    {
        if (is_int($version)) {
            return $version;
        }

        if (is_string($version) && ctype_digit($version)) {
            return (int) $version;
        }

        return null;
    }
}
