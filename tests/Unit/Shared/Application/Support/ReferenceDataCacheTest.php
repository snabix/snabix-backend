<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Application\Support;

use App\Shared\Application\Support\ReferenceDataCache;
use Illuminate\Concurrency\ProcessDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Concurrency;
use RuntimeException;
use Tests\TestCase;

class ReferenceDataCacheTest extends TestCase
{
    private const string CATALOG_VERSION_KEY = 'reference-data:catalog:version';

    protected function tearDown(): void
    {
        Cache::store('redis')->forget(self::CATALOG_VERSION_KEY);
        Cache::store('redis')->forget(self::CATALOG_VERSION_KEY . ':lock');
        config(['cache.default' => 'array']);

        parent::tearDown();
    }

    public function test_catalog_batch_invalidates_once_and_discards_failed_batch(): void
    {
        Cache::flush();
        Cache::forever(self::CATALOG_VERSION_KEY, 1);

        $cache     = app(ReferenceDataCache::class);
        $result    = $cache->batchCatalogInvalidation(function () use ($cache): string {
            $cache->invalidateCatalog();
            $cache->batchCatalogInvalidation(function () use ($cache): void {
                $cache->invalidateCatalog();
                $cache->invalidateCatalog();
            });

            return 'committed';
        });

        $this->assertSame('committed', $result);
        $this->assertSame(2, $this->cacheVersion());

        $exception = null;

        try {
            $cache->batchCatalogInvalidation(function () use ($cache): void {
                $cache->invalidateCatalog();

                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $caughtException) {
            $exception = $caughtException;
        }

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertSame('rollback', $exception->getMessage());
        $this->assertSame(2, $this->cacheVersion());
    }

    public function test_concurrent_catalog_invalidations_use_atomic_version_increment(): void
    {
        config(['cache.default' => 'redis']);
        Cache::store('redis')->forever(self::CATALOG_VERSION_KEY, 1);

        $tasks   = array_fill(0, 8, static function (): bool {
            config(['cache.default' => 'redis']);
            app(ReferenceDataCache::class)->invalidateCatalog();

            return true;
        });

        $driver  = Concurrency::driver('process');

        $this->assertInstanceOf(ProcessDriver::class, $driver);
        $results = $driver->run($tasks);

        $this->assertSame(array_fill(0, 8, true), array_values($results));
        $this->assertSame(9, $this->cacheVersion('redis'));
    }

    private function cacheVersion(?string $store = null): int
    {
        $version = $store === null
            ? Cache::get(self::CATALOG_VERSION_KEY)
            : Cache::store($store)->get(self::CATALOG_VERSION_KEY);

        if (is_int($version)) {
            return $version;
        }

        if (is_string($version) && ctype_digit($version)) {
            return (int) $version;
        }

        $this->fail('Cache version must be an integer or a numeric string.');
    }
}
