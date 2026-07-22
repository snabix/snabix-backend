<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use App\Location\Domain\Enums\LocationImportStatus;
use App\Location\Infrastructure\Models\EloquentLocationImportManifest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Throwable;

class LocationImportStager
{
    private const string REGION_KIND = 'region';

    private const string CITY_KIND   = 'city';

    public function __construct(
        private readonly RussiaLocationPayloadNormalizer $normalizer,
        private readonly LocationImportPreviewCalculator $previewCalculator,
        private readonly LocationImportRecovery $recovery,
    ) {}

    /**
     * @throws Throwable
     */
    public function prepare(string $regionsPath, string $citiesPath, bool $fresh): EloquentLocationImportManifest
    {
        memory_reset_peak_usage();
        $this->recovery->cleanupAbandonedImports();

        $regionsChecksum = $this->checksum($regionsPath);
        $citiesChecksum  = $this->checksum($citiesPath);
        $startedAt       = microtime(true);
        $manifest        = EloquentLocationImportManifest::query()->create([
            'id'                 => (string) Str::uuid(),
            'source'             => 'russia',
            'source_version'     => hash('sha256', $regionsChecksum . ':' . $citiesChecksum),
            'regions_file'       => basename($regionsPath),
            'cities_file'        => basename($citiesPath),
            'regions_checksum'   => $regionsChecksum,
            'cities_checksum'    => $citiesChecksum,
            'regions_size_bytes' => $this->fileSize($regionsPath),
            'cities_size_bytes'  => $this->fileSize($citiesPath),
            'fresh'              => $fresh,
            'status'             => LocationImportStatus::PREPARING,
            'started_at'         => now(),
        ]);

        try {
            $regionCount = $this->stageRegions($manifest->id, $regionsPath);
            $cityCount   = $this->stageCities($manifest->id, $citiesPath);

            $this->assertCityRegionsExist($manifest->id);

            $stats       = [
                ...$this->previewCalculator->calculate($manifest->id, $fresh),
                'regions_total'      => $regionCount,
                'cities_total'       => $cityCount,
                'prepare_duration_ms'=> (int) round((microtime(true) - $startedAt) * 1000),
                'peak_memory_bytes'  => memory_get_peak_usage(true),
            ];

            $manifest->fill([
                'status' => LocationImportStatus::PREVIEW,
                'stats'  => $stats,
            ])->save();

            return $manifest->fresh() ?? $manifest;
        } catch (Throwable $exception) {
            $this->discard($manifest->id);
            $manifest->fill([
                'status'        => LocationImportStatus::FAILED,
                'error_message' => $exception->getMessage(),
                'completed_at'  => now(),
            ])->save();

            throw $exception;
        }
    }

    public function discard(string $manifestId): void
    {
        DB::table('location_import_staging')
            ->where('manifest_id', $manifestId)
            ->delete();
    }

    public function completePreview(EloquentLocationImportManifest $manifest): void
    {
        DB::transaction(function () use ($manifest): void {
            $this->discard($manifest->id);

            $manifest->fill([
                'status'       => LocationImportStatus::PREVIEWED,
                'completed_at' => now(),
            ])->save();
        });
    }

    private function stageRegions(string $manifestId, string $path): int
    {
        return $this->stageFile(
            manifestId: $manifestId,
            path: $path,
            kind: self::REGION_KIND,
            normalize: fn(array $payload, int $sortOrder): array => [
                'parent_external_id' => null,
                'attributes'         => $this->normalizer->region($payload, $sortOrder),
            ],
        );
    }

    private function stageCities(string $manifestId, string $path): int
    {
        return $this->stageFile(
            manifestId: $manifestId,
            path: $path,
            kind: self::CITY_KIND,
            normalize: function (array $payload, int $sortOrder): array {
                $city = $this->normalizer->city($payload, $sortOrder);

                return [
                    'parent_external_id' => $city['region_kladr_id'],
                    'attributes'         => $city['attributes'],
                ];
            },
        );
    }

    /**
     * @param callable(array<string, mixed>, int): array{parent_external_id: ?string, attributes: array<string, mixed>} $normalize
     *
     * @throws JsonException
     */
    private function stageFile(
        string $manifestId,
        string $path,
        string $kind,
        callable $normalize,
    ): int {
        $buffer = [];
        $count  = 0;

        foreach (Items::fromFile($path, ['decoder' => new ExtJsonDecoder(true)]) as $payload) {
            if (! is_array($payload)) {
                throw new InvalidArgumentException(sprintf(
                    'Запись #%d в [%s] должна быть JSON-объектом.',
                    $count,
                    basename($path),
                ));
            }

            /** @var array<string, mixed> $payload */
            $normalized  = $normalize($payload, $count);
            $attributes  = $normalized['attributes'];
            $externalId  = $attributes['kladr_id'] ?? null;

            if (! is_string($externalId) || $externalId === '') {
                throw new InvalidArgumentException(sprintf('Запись #%d не содержит KLADR ID.', $count));
            }

            $buffer[]    = [
                'manifest_id'       => $manifestId,
                'kind'              => $kind,
                'external_id'       => $externalId,
                'parent_external_id'=> $normalized['parent_external_id'],
                'sort_order'        => $count,
                'payload'           => json_encode(
                    $attributes,
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                ),
            ];
            $count++;

            if (count($buffer) >= $this->chunkSize()) {
                DB::table('location_import_staging')->insert($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('location_import_staging')->insert($buffer);
        }

        return $count;
    }

    private function assertCityRegionsExist(string $manifestId): void
    {
        $missingRegion = DB::table('location_import_staging as cities')
            ->leftJoin('location_import_staging as regions', function ($join) use ($manifestId): void {
                $join
                    ->on('regions.external_id', '=', 'cities.parent_external_id')
                    ->where('regions.manifest_id', '=', $manifestId)
                    ->where('regions.kind', '=', self::REGION_KIND);
            })
            ->where('cities.manifest_id', $manifestId)
            ->where('cities.kind', self::CITY_KIND)
            ->whereNull('regions.id')
            ->value('cities.parent_external_id');

        if (is_string($missingRegion)) {
            throw new InvalidArgumentException(sprintf(
                'Не найден регион [%s] для одной или нескольких записей городов.',
                $missingRegion,
            ));
        }
    }

    private function checksum(string $path): string
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Файл [%s] не найден или недоступен для чтения.', $path));
        }

        $checksum = hash_file('sha256', $path);

        if (! is_string($checksum)) {
            throw new InvalidArgumentException(sprintf('Не удалось вычислить checksum файла [%s].', $path));
        }

        return $checksum;
    }

    private function fileSize(string $path): int
    {
        $size = filesize($path);

        if (! is_int($size)) {
            throw new InvalidArgumentException(sprintf('Не удалось определить размер файла [%s].', $path));
        }

        return $size;
    }

    private function chunkSize(): int
    {
        $chunkSize = config('location-import.staging_chunk_size', 250);

        return is_int($chunkSize) && $chunkSize > 0 ? $chunkSize : 250;
    }
}
