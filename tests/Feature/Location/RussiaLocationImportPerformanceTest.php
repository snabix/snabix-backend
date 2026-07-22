<?php

declare(strict_types=1);

namespace Tests\Feature\Location;

use App\Location\Application\Services\RussiaLocationImporter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Feature\FeatureTestCase;

class RussiaLocationImportPerformanceTest extends FeatureTestCase
{
    #[RunInSeparateProcess]
    public function test_full_reference_dataset_stays_within_import_budgets_and_is_repeatable(): void
    {
        [$regionsPath, $citiesPath] = $this->createFullCardinalityFixture();
        $this->assertDatabaseCount('regions', 0);

        $queryCount                 = 0;

        DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
            if (
                str_contains($query->sql, 'location_import')
                || str_contains($query->sql, 'regions')
                || str_contains($query->sql, 'cities')
            ) {
                $queryCount++;
            }
        });

        $importer                   = app(RussiaLocationImporter::class);
        $memoryBudget               = $this->integerConfig('location-import.budgets.peak_memory_bytes');
        $durationBudget             = $this->integerConfig('location-import.budgets.duration_seconds');
        $queryBudget                = $this->integerConfig('location-import.budgets.query_count');
        $first                      = $importer->import(
            $regionsPath,
            $citiesPath,
        );

        $this->assertSame(83, $first['regions_total'] ?? null);
        $this->assertSame(1102, $first['cities_total'] ?? null);
        $this->assertDatabaseCount('regions', 83);
        $this->assertDatabaseCount('cities', 1102);
        $this->assertLessThanOrEqual(
            $memoryBudget,
            $first['peak_memory_bytes'] ?? PHP_INT_MAX,
        );
        $this->assertLessThanOrEqual(
            $durationBudget * 1000,
            $first['total_duration_ms'] ?? PHP_INT_MAX,
        );
        $this->assertLessThanOrEqual(
            $queryBudget,
            $queryCount,
        );

        $second                     = $importer->import(
            $regionsPath,
            $citiesPath,
        );

        $this->assertSame(0, $second['regions_created'] ?? null);
        $this->assertSame(83, $second['regions_updated'] ?? null);
        $this->assertSame(0, $second['cities_created'] ?? null);
        $this->assertSame(1102, $second['cities_updated'] ?? null);
        $this->assertDatabaseCount('regions', 83);
        $this->assertDatabaseCount('cities', 1102);
        $this->assertDatabaseCount('location_import_staging', 0);
    }

    private function integerConfig(string $key): int
    {
        $value = config($key);

        $this->assertIsInt($value);

        return $value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function createFullCardinalityFixture(): array
    {
        $regions     = [];
        $cities      = [];

        for ($regionNumber = 1; $regionNumber <= 83; $regionNumber++) {
            $regionId  = sprintf('%02d00000000000', $regionNumber);
            $regions[] = [
                'id'          => $regionId,
                'name'        => 'Регион ' . $regionNumber,
                'label'       => 'region-' . $regionNumber,
                'contentType' => 'region',
            ];
        }

        for ($cityNumber = 1; $cityNumber <= 1102; $cityNumber++) {
            $regionNumber = (($cityNumber - 1) % 83) + 1;
            $cities[]     = [
                'id'          => sprintf('%013d', 1000000000000 + $cityNumber),
                'name'        => 'Город ' . $cityNumber,
                'label'       => 'city-' . $cityNumber,
                'contentType' => 'city',
                'region'      => [
                    'id' => sprintf('%02d00000000000', $regionNumber),
                ],
            ];
        }

        $regionsPath = tempnam(sys_get_temp_dir(), 'snabix-full-regions-');
        $citiesPath  = tempnam(sys_get_temp_dir(), 'snabix-full-cities-');

        $this->assertIsString($regionsPath);
        $this->assertIsString($citiesPath);
        file_put_contents(
            $regionsPath,
            json_encode($regions, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );
        file_put_contents(
            $citiesPath,
            json_encode($cities, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        );

        return [$regionsPath, $citiesPath];
    }
}
