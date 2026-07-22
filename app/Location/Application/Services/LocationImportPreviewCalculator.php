<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LocationImportPreviewCalculator
{
    private const string REGION_KIND = 'region';

    private const string CITY_KIND   = 'city';

    /**
     * @return array{regions_created: int, regions_updated: int, regions_deactivated: int, cities_created: int, cities_updated: int, cities_deactivated: int}
     */
    public function calculate(string $manifestId, bool $fresh): array
    {
        $regions = $this->stagingQuery($manifestId, self::REGION_KIND);
        $cities  = $this->stagingQuery($manifestId, self::CITY_KIND);

        if ($fresh) {
            return [
                'regions_created'     => (clone $regions)->count(),
                'regions_updated'     => 0,
                'regions_deactivated' => 0,
                'cities_created'      => (clone $cities)->count(),
                'cities_updated'      => 0,
                'cities_deactivated'  => 0,
            ];
        }

        return [
            'regions_created'     => $this->newCount(clone $regions, 'regions'),
            'regions_updated'     => $this->existingCount(clone $regions, 'regions'),
            'regions_deactivated' => $this->missingActiveCount($manifestId, self::REGION_KIND, 'regions'),
            'cities_created'      => $this->newCount(clone $cities, 'cities'),
            'cities_updated'      => $this->existingCount(clone $cities, 'cities'),
            'cities_deactivated'  => $this->missingActiveCount($manifestId, self::CITY_KIND, 'cities'),
        ];
    }

    private function stagingQuery(string $manifestId, string $kind): Builder
    {
        return DB::table('location_import_staging')
            ->where('manifest_id', $manifestId)
            ->where('kind', $kind);
    }

    private function newCount(Builder $staging, string $targetTable): int
    {
        return $staging
            ->leftJoin($targetTable . ' as target', 'target.kladr_id', '=', 'location_import_staging.external_id')
            ->whereNull('target.id')
            ->count();
    }

    private function existingCount(Builder $staging, string $targetTable): int
    {
        return $staging
            ->join($targetTable . ' as target', 'target.kladr_id', '=', 'location_import_staging.external_id')
            ->count();
    }

    private function missingActiveCount(string $manifestId, string $kind, string $targetTable): int
    {
        return DB::table($targetTable . ' as target')
            ->where('target.is_active', true)
            ->whereNotExists(function (Builder $query) use ($manifestId, $kind): void {
                $query
                    ->selectRaw('1')
                    ->from('location_import_staging as stage')
                    ->where('stage.manifest_id', $manifestId)
                    ->where('stage.kind', $kind)
                    ->whereColumn('stage.external_id', 'target.kladr_id');
            })
            ->count();
    }
}
