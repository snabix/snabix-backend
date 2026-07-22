<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use App\Location\Infrastructure\Models\EloquentLocationImportManifest;
use Closure;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

class RussiaLocationImporter
{
    public function __construct(
        private readonly LocationImportStager $stager,
        private readonly LocationImportPromoter $promoter,
    ) {}

    /**
     * @return array<string, int|string>
     *
     * @throws Throwable
     */
    public function import(
        string $regionsPath,
        string $citiesPath,
        bool $fresh = false,
    ): array {
        return $this->withImportLock(function () use ($regionsPath, $citiesPath, $fresh): array {
            $startedAt = microtime(true);
            $manifest  = $this->stager->prepare($regionsPath, $citiesPath, $fresh);
            $stats     = $this->promoter->apply($manifest);

            return [
                ...$stats,
                'manifest_id'       => $manifest->id,
                'source_version'    => $manifest->source_version,
                'total_duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];
        });
    }

    /**
     * @return array<string, int|string>
     *
     * @throws Throwable
     */
    public function preview(
        string $regionsPath,
        string $citiesPath,
        bool $fresh = false,
    ): array {
        return $this->withImportLock(function () use ($regionsPath, $citiesPath, $fresh): array {
            $manifest = $this->stager->prepare($regionsPath, $citiesPath, $fresh);
            $stats    = $this->manifestStats($manifest);

            $this->stager->completePreview($manifest);

            return [
                ...$stats,
                'regions'        => $stats['regions_total'] ?? 0,
                'cities'         => $stats['cities_total'] ?? 0,
                'manifest_id'    => $manifest->id,
                'source_version' => $manifest->source_version,
            ];
        });
    }

    /**
     * @return array<string, int>
     */
    private function manifestStats(EloquentLocationImportManifest $manifest): array
    {
        return $manifest->stats ?? [];
    }

    /**
     * @param  Closure(): array<string, int|string> $callback
     * @return array<string, int|string>
     *
     * @throws Throwable
     */
    private function withImportLock(Closure $callback): array
    {
        $lockSeconds = config('location-import.lock_seconds', 300);
        $waitSeconds = config('location-import.lock_wait_seconds', 5);

        if (! is_int($lockSeconds) || $lockSeconds < 1) {
            $lockSeconds = 300;
        }

        if (! is_int($waitSeconds) || $waitSeconds < 0) {
            $waitSeconds = 5;
        }

        $result      = Cache::lock('location-import:russia', $lockSeconds)->block($waitSeconds, $callback);

        if (! is_array($result)) {
            throw new RuntimeException('Импорт локаций вернул результат не в формате массива.');
        }

        $normalized  = [];

        foreach ($result as $key => $value) {
            if (! is_string($key) || (! is_int($value) && ! is_string($value))) {
                throw new RuntimeException('Импорт локаций вернул некорректную статистику.');
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
