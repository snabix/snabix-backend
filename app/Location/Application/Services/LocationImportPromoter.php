<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use App\Location\Domain\Enums\LocationImportStatus;
use App\Location\Infrastructure\Models\EloquentLocationImportManifest;
use App\Shared\Application\Support\ReferenceDataCache;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use stdClass;
use Throwable;

class LocationImportPromoter
{
    private const string REGION_KIND = 'region';

    private const string CITY_KIND   = 'city';

    public function __construct(
        private readonly ReferenceDataCache $cache,
        private readonly LocationImportStager $stager,
    ) {}

    /**
     * @return array<string, int>
     *
     * @throws Throwable
     */
    public function apply(EloquentLocationImportManifest $manifest): array
    {
        try {
            $stats = $this->cache->batchLocationInvalidation(
                fn(): array => DB::transaction(function () use ($manifest): array {
                    $lockedManifest = EloquentLocationImportManifest::query()
                        ->lockForUpdate()
                        ->findOrFail($manifest->id);

                    if ($lockedManifest->status !== LocationImportStatus::PREVIEW) {
                        throw new RuntimeException(sprintf(
                            'Manifest [%s] не готов к применению: %s.',
                            $lockedManifest->id,
                            $lockedManifest->status->value,
                        ));
                    }

                    $lockedManifest->fill(['status' => LocationImportStatus::APPLYING])->save();

                    if ($lockedManifest->fresh) {
                        DB::table('cities')->delete();
                        DB::table('regions')->delete();
                    }

                    $this->upsertRegions($lockedManifest->id);
                    $regionIds      = $this->regionIds($lockedManifest->id);
                    $this->upsertCities($lockedManifest->id, $regionIds);

                    if (! $lockedManifest->fresh) {
                        $this->deactivateMissing($lockedManifest->id, self::CITY_KIND, 'cities');
                        $this->deactivateMissing($lockedManifest->id, self::REGION_KIND, 'regions');
                    }

                    $stats          = $lockedManifest->stats ?? [];
                    $lockedManifest->fill([
                        'status'       => LocationImportStatus::APPLIED,
                        'completed_at' => now(),
                    ])->save();

                    $this->cache->invalidateLocation();

                    return $stats;
                }),
            );

            $this->stager->discard($manifest->id);

            return $stats;
        } catch (Throwable $exception) {
            $currentManifest = $manifest->fresh();

            if (
                $currentManifest instanceof EloquentLocationImportManifest
                && $currentManifest->status !== LocationImportStatus::APPLIED
            ) {
                $currentManifest->fill([
                    'status'        => LocationImportStatus::FAILED,
                    'error_message' => $exception->getMessage(),
                    'completed_at'  => now(),
                ])->save();
            }

            $this->stager->discard($manifest->id);

            throw $exception;
        }
    }

    /**
     * @throws JsonException
     */
    private function upsertRegions(string $manifestId): void
    {
        $this->stagingQuery($manifestId, self::REGION_KIND)
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function (Collection $stagingRows): void {
                $now  = now();
                $rows = $stagingRows
                    ->map(function (stdClass $stagingRow) use ($now): array {
                        return [
                            ...$this->payload($stagingRow),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->all();

                DB::table('regions')->upsert(
                    $rows,
                    ['kladr_id'],
                    $this->regionUpdateColumns(),
                );
            });
    }

    /**
     * @param array<string, int> $regionIds
     *
     * @throws JsonException
     */
    private function upsertCities(string $manifestId, array $regionIds): void
    {
        $this->stagingQuery($manifestId, self::CITY_KIND)
            ->orderBy('id')
            ->chunkById($this->chunkSize(), function (Collection $stagingRows) use ($regionIds): void {
                $now  = now();
                $rows = $stagingRows
                    ->map(function (stdClass $stagingRow) use ($now, $regionIds): array {
                        $regionKladrId = $stagingRow->parent_external_id ?? null;

                        if (! is_string($regionKladrId) || ! isset($regionIds[$regionKladrId])) {
                            throw new InvalidArgumentException(sprintf(
                                'Не найден примененный регион [%s] для города.',
                                is_scalar($regionKladrId) ? (string) $regionKladrId : 'null',
                            ));
                        }

                        return [
                            ...$this->payload($stagingRow),
                            'region_id' => $regionIds[$regionKladrId],
                            'created_at'=> $now,
                            'updated_at'=> $now,
                        ];
                    })
                    ->all();

                DB::table('cities')->upsert(
                    $rows,
                    ['kladr_id'],
                    $this->cityUpdateColumns(),
                );
            });
    }

    /**
     * @return array<string, int>
     */
    private function regionIds(string $manifestId): array
    {
        /** @var array<string, int> $regionIds */
        $regionIds = DB::table('regions')
            ->join('location_import_staging as stage', 'stage.external_id', '=', 'regions.kladr_id')
            ->where('stage.manifest_id', $manifestId)
            ->where('stage.kind', self::REGION_KIND)
            ->pluck('regions.id', 'regions.kladr_id')
            ->map(static function (mixed $id): int {
                if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
                    throw new InvalidArgumentException('База данных вернула некорректный ID региона.');
                }

                return (int) $id;
            })
            ->all();

        return $regionIds;
    }

    private function deactivateMissing(string $manifestId, string $kind, string $targetTable): void
    {
        DB::table($targetTable . ' as target')
            ->where('target.is_active', true)
            ->whereNotExists(function (Builder $query) use ($manifestId, $kind): void {
                $query
                    ->selectRaw('1')
                    ->from('location_import_staging as stage')
                    ->where('stage.manifest_id', $manifestId)
                    ->where('stage.kind', $kind)
                    ->whereColumn('stage.external_id', 'target.kladr_id');
            })
            ->update([
                'is_active'  => false,
                'updated_at' => now(),
            ]);
    }

    private function stagingQuery(string $manifestId, string $kind): Builder
    {
        return DB::table('location_import_staging')
            ->where('manifest_id', $manifestId)
            ->where('kind', $kind)
            ->select(['id', 'parent_external_id', 'payload']);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function payload(stdClass $stagingRow): array
    {
        $payload = $stagingRow->payload ?? null;

        if (! is_string($payload)) {
            throw new InvalidArgumentException('Staging payload должен быть JSON-строкой.');
        }

        $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Staging payload должен содержать JSON-объект.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @return list<string>
     */
    private function regionUpdateColumns(): array
    {
        return [
            'fias_guid', 'name', 'slug', 'label', 'type', 'type_short', 'content_type',
            'okato', 'oktmo', 'code', 'iso_code', 'population', 'year_founded', 'area',
            'fullname', 'unofficial_name', 'name_en', 'district', 'name_cases', 'capital_data',
            'is_active', 'sort_order', 'updated_at',
        ];
    }

    /**
     * @return list<string>
     */
    private function cityUpdateColumns(): array
    {
        return [
            'region_id', 'fias_guid', 'name', 'name_alt', 'slug', 'label', 'type', 'type_short',
            'content_type', 'okato', 'oktmo', 'zip', 'population', 'year_founded',
            'year_city_status', 'name_en', 'name_cases', 'lat', 'lon', 'timezone', 'is_capital',
            'is_dual_name', 'is_active', 'sort_order', 'updated_at',
        ];
    }

    private function chunkSize(): int
    {
        $chunkSize = config('location-import.promotion_chunk_size', 250);

        return is_int($chunkSize) && $chunkSize > 0 ? $chunkSize : 250;
    }
}
