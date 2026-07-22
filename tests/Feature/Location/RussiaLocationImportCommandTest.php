<?php

declare(strict_types=1);

namespace Tests\Feature\Location;

use App\Location\Application\Services\LocationImportPromoter;
use App\Location\Application\Services\LocationImportStager;
use App\Location\Domain\Enums\LocationImportStatus;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentLocationImportManifest;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\Feature\FeatureTestCase;

class RussiaLocationImportCommandTest extends FeatureTestCase
{
    public function test_russia_locations_can_be_imported_from_json_files(): void
    {
        Cache::forever('reference-data:location:version', 1);

        [$regionsPath, $citiesPath] = $this->createLocationFiles(
            regionPopulation: 4091423,
            cityPopulation: 64041,
        );

        $exitCode                   = Artisan::call('location:import-russia', [
            '--regions' => $regionsPath,
            '--cities'  => $citiesPath,
        ]);

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('regions', [
            'kladr_id'   => '0200000000000',
            'name'       => 'Башкортостан',
            'slug'       => 'bashkortostan',
            'iso_code'   => 'RU-BA',
            'population' => 4091423,
        ]);
        $this->assertDatabaseHas('cities', [
            'kladr_id'        => '0202600100000',
            'name'            => 'Ишимбай',
            'population'      => 64041,
            'year_founded'    => '1932',
            'year_city_status'=> '1940',
            'is_capital'      => false,
        ]);

        $region                     = EloquentRegion::query()->where('kladr_id', '0200000000000')->first();
        $city                       = EloquentCity::query()->where('kladr_id', '0202600100000')->first();

        $this->assertInstanceOf(EloquentRegion::class, $region);
        $this->assertInstanceOf(EloquentCity::class, $city);
        $this->assertSame($region->id, $city->region_id);
        $this->assertSame('Asia/Yekaterinburg', $city->timezone['tzid'] ?? null);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'location.import',
            'action'   => 'import_russia_locations',
        ]);
        $this->assertDatabaseHas('location_import_manifests', [
            'status' => LocationImportStatus::APPLIED->value,
        ]);
        $this->assertDatabaseCount('location_import_staging', 0);
        $this->assertEquals(2, Cache::get('reference-data:location:version'));

        $manifest                   = EloquentLocationImportManifest::query()->firstOrFail();
        $regionsChecksum            = hash_file('sha256', $regionsPath);
        $citiesChecksum             = hash_file('sha256', $citiesPath);

        $this->assertIsString($regionsChecksum);
        $this->assertIsString($citiesChecksum);
        $this->assertSame($regionsChecksum, $manifest->regions_checksum);
        $this->assertSame($citiesChecksum, $manifest->cities_checksum);
        $this->assertSame(
            hash('sha256', $regionsChecksum . ':' . $citiesChecksum),
            $manifest->source_version,
        );
    }

    public function test_russia_locations_import_is_idempotent_and_can_update_existing_rows(): void
    {
        [$regionsPath, $citiesPath]               = $this->createLocationFiles(
            regionPopulation: 4091423,
            cityPopulation: 64041,
        );

        Artisan::call('location:import-russia', [
            '--regions' => $regionsPath,
            '--cities'  => $citiesPath,
        ]);

        [$updatedRegionsPath, $updatedCitiesPath] = $this->createLocationFiles(
            regionPopulation: 4100000,
            cityPopulation: 65000,
        );

        $exitCode                                 = Artisan::call('location:import-russia', [
            '--regions' => $updatedRegionsPath,
            '--cities'  => $updatedCitiesPath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, EloquentRegion::query()->where('kladr_id', '0200000000000')->count());
        $this->assertSame(1, EloquentCity::query()->where('kladr_id', '0202600100000')->count());
        $this->assertDatabaseHas('regions', [
            'kladr_id'   => '0200000000000',
            'population' => 4100000,
        ]);
        $this->assertDatabaseHas('cities', [
            'kladr_id'   => '0202600100000',
            'population' => 65000,
        ]);
    }

    public function test_missing_source_records_are_deactivated_without_deleting_them(): void
    {
        [$regionsPath, $citiesPath]               = $this->writeLocationFiles(
            [
                $this->regionPayload(),
                $this->regionPayload(
                    id: '0300000000000',
                    name: 'Бурятия',
                    label: 'buryatiya',
                    guid: 'a84ebed3-153d-4ba9-8532-8bdf879e1f5a',
                    code: '03',
                    isoCode: 'RU-BU',
                ),
            ],
            [
                $this->cityPayload(),
                $this->cityPayload(
                    id: '0300000100000',
                    name: 'Улан-Удэ',
                    label: 'ulan-ude',
                    guid: '9fdcc25f-a3d0-4f28-8b61-40648d099065',
                    regionId: '0300000000000',
                ),
            ],
        );

        Artisan::call('location:import-russia', [
            '--regions' => $regionsPath,
            '--cities'  => $citiesPath,
        ]);

        $existingManifestIds                      = EloquentLocationImportManifest::query()->pluck('id');

        [$reducedRegionsPath, $reducedCitiesPath] = $this->createLocationFiles(
            regionPopulation: 4100000,
            cityPopulation: 65000,
        );

        $exitCode                                 = Artisan::call('location:import-russia', [
            '--regions' => $reducedRegionsPath,
            '--cities'  => $reducedCitiesPath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('regions', [
            'kladr_id' => '0200000000000',
            'is_active'=> true,
        ]);
        $this->assertDatabaseHas('cities', [
            'kladr_id' => '0202600100000',
            'is_active'=> true,
        ]);
        $this->assertDatabaseHas('regions', [
            'kladr_id' => '0300000000000',
            'is_active'=> false,
        ]);
        $this->assertDatabaseHas('cities', [
            'kladr_id' => '0300000100000',
            'is_active'=> false,
        ]);

        $manifest                                 = EloquentLocationImportManifest::query()
            ->whereNotIn('id', $existingManifestIds)
            ->firstOrFail();

        $this->assertSame(1, $manifest->stats['regions_deactivated'] ?? null);
        $this->assertSame(1, $manifest->stats['cities_deactivated'] ?? null);
    }

    public function test_dry_run_persists_manifest_but_does_not_change_reference_data(): void
    {
        [$regionsPath, $citiesPath] = $this->createLocationFiles(
            regionPopulation: 4091423,
            cityPopulation: 64041,
        );

        $exitCode                   = Artisan::call('location:import-russia', [
            '--regions' => $regionsPath,
            '--cities'  => $citiesPath,
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('regions', 0);
        $this->assertDatabaseCount('cities', 0);
        $this->assertDatabaseHas('location_import_manifests', [
            'status' => LocationImportStatus::PREVIEWED->value,
        ]);
        $this->assertDatabaseCount('location_import_staging', 0);
    }

    public function test_failed_promotion_rolls_back_reference_data_and_cleans_staging(): void
    {
        [$regionsPath, $citiesPath]               = $this->createLocationFiles(
            regionPopulation: 4091423,
            cityPopulation: 64041,
        );

        Artisan::call('location:import-russia', [
            '--regions' => $regionsPath,
            '--cities'  => $citiesPath,
        ]);

        Cache::forever('reference-data:location:version', 10);

        [$updatedRegionsPath, $updatedCitiesPath] = $this->createLocationFiles(
            regionPopulation: 5000000,
            cityPopulation: 70000,
        );

        $stager                                   = app(LocationImportStager::class);
        $promoter                                 = app(LocationImportPromoter::class);
        $manifest                                 = $stager->prepare($updatedRegionsPath, $updatedCitiesPath, false);

        DB::table('location_import_staging')
            ->where('manifest_id', $manifest->id)
            ->where('kind', 'city')
            ->update(['payload' => json_encode('invalid-payload', JSON_THROW_ON_ERROR)]);

        try {
            $promoter->apply($manifest);
            $this->fail('Promotion with a broken city payload must fail.');
        } catch (InvalidArgumentException) {
            // The region upsert happens first; the surrounding transaction must roll it back.
        }

        $this->assertDatabaseHas('regions', [
            'kladr_id'   => '0200000000000',
            'population' => 4091423,
        ]);
        $this->assertDatabaseHas('cities', [
            'kladr_id'   => '0202600100000',
            'population' => 64041,
        ]);
        $this->assertEquals(10, Cache::get('reference-data:location:version'));
        $this->assertDatabaseHas('location_import_manifests', [
            'id'     => $manifest->id,
            'status' => LocationImportStatus::FAILED->value,
        ]);
        $this->assertDatabaseCount('location_import_staging', 0);
    }

    public function test_next_run_marks_abandoned_staging_as_failed_and_cleans_it(): void
    {
        [$regionsPath, $citiesPath] = $this->createLocationFiles(
            regionPopulation: 4091423,
            cityPopulation: 64041,
        );

        $stager                     = app(LocationImportStager::class);
        $abandoned                  = $stager->prepare($regionsPath, $citiesPath, false);
        $abandoned->fill([
            'status'     => LocationImportStatus::PREPARING,
            'started_at' => now()->subHours(2),
        ])->save();

        $replacement                = $stager->prepare($regionsPath, $citiesPath, false);

        $this->assertDatabaseHas('location_import_manifests', [
            'id'     => $abandoned->id,
            'status' => LocationImportStatus::FAILED->value,
        ]);
        $this->assertDatabaseMissing('location_import_staging', [
            'manifest_id' => $abandoned->id,
        ]);

        $stager->completePreview($replacement);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function createLocationFiles(int $regionPopulation, int $cityPopulation): array
    {
        return $this->writeLocationFiles(
            [$this->regionPayload(population: $regionPopulation)],
            [$this->cityPayload(population: $cityPopulation)],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $regions
     * @param  list<array<string, mixed>>  $cities
     * @return array{0: string, 1: string}
     */
    private function writeLocationFiles(array $regions, array $cities): array
    {
        $regionsPath = tempnam(sys_get_temp_dir(), 'snabix-regions-');
        $citiesPath  = tempnam(sys_get_temp_dir(), 'snabix-cities-');

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

    /**
     * @return array<string, mixed>
     */
    private function regionPayload(
        int $population = 4091423,
        string $id = '0200000000000',
        string $name = 'Башкортостан',
        string $label = 'bashkortostan',
        string $guid = '6f2cbfd8-692a-4ee4-9b16-067210bde3fc',
        string $code = '02',
        string $isoCode = 'RU-BA',
    ): array {
        return [
            'name'       => $name,
            'label'      => $label,
            'type'       => 'Республика',
            'typeShort'  => 'Респ',
            'contentType'=> 'region',
            'id'         => $id,
            'okato'      => '80000000000',
            'oktmo'      => '80000000',
            'guid'       => $guid,
            'code'       => $code,
            'iso_3166-2' => $isoCode,
            'population' => $population,
            'yearFounded'=> 1919,
            'area'       => 142947,
            'fullname'   => 'Республика ' . $name,
            'name_en'    => $name,
            'district'   => 'Приволжский',
            'namecase'   => [
                'nominative' => $name,
                'locative'   => $name,
            ],
            'capital'    => [
                'name'        => 'Уфа',
                'label'       => 'ufa',
                'id'          => '0200000100000',
                'contentType' => 'city',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cityPayload(
        int $population = 64041,
        string $id = '0202600100000',
        string $name = 'Ишимбай',
        string $label = 'ishimbay',
        string $guid = 'b3d2e10f-752d-4b90-b54b-9d6545f38ae0',
        string $regionId = '0200000000000',
    ): array {
        return [
            'name'          => $name,
            'name_alt'      => $name,
            'label'         => $label,
            'type'          => 'Город',
            'typeShort'     => 'г',
            'contentType'   => 'city',
            'id'            => $id,
            'okato'         => '80420000000',
            'oktmo'         => '80631101001',
            'guid'          => $guid,
            'isDualName'    => false,
            'isCapital'     => false,
            'zip'           => 453201,
            'population'    => $population,
            'yearFounded'   => 1932,
            'yearCityStatus'=> 1940,
            'name_en'       => $name,
            'namecase'      => [
                'nominative' => $name,
                'locative'   => $name,
            ],
            'coords'        => [
                'lat' => 53.4545764,
                'lon' => 56.0438751,
            ],
            'timezone'      => [
                'tzid'         => 'Asia/Yekaterinburg',
                'abbreviation' => 'YEKT',
                'utcOffset'    => 'UTC+05:00',
                'mskOffset'    => 'MSK+02',
            ],
            'region'        => [
                'id'    => $regionId,
                'name'  => 'Регион',
                'label' => 'region',
            ],
        ];
    }
}
