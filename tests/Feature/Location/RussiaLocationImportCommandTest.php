<?php

declare(strict_types=1);

namespace Tests\Feature\Location;

use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\FeatureTestCase;

class RussiaLocationImportCommandTest extends FeatureTestCase
{
    public function test_russia_locations_can_be_imported_from_json_files(): void
    {
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

    /**
     * @return array{0: string, 1: string}
     */
    private function createLocationFiles(int $regionPopulation, int $cityPopulation): array
    {
        $regionsPath = tempnam(sys_get_temp_dir(), 'snabix-regions-');
        $citiesPath  = tempnam(sys_get_temp_dir(), 'snabix-cities-');

        $this->assertIsString($regionsPath);
        $this->assertIsString($citiesPath);

        file_put_contents($regionsPath, json_encode([
            [
                'name'       => 'Башкортостан',
                'label'      => 'bashkortostan',
                'type'       => 'Республика',
                'typeShort'  => 'Респ',
                'contentType'=> 'region',
                'id'         => '0200000000000',
                'okato'      => '80000000000',
                'oktmo'      => '80000000',
                'guid'       => '6f2cbfd8-692a-4ee4-9b16-067210bde3fc',
                'code'       => '02',
                'iso_3166-2' => 'RU-BA',
                'population' => $regionPopulation,
                'yearFounded'=> 1919,
                'area'       => 142947,
                'fullname'   => 'Республика Башкортостан',
                'name_en'    => 'Republic of Bashkortostan',
                'district'   => 'Приволжский',
                'namecase'   => [
                    'nominative' => 'Республика Башкортостан',
                    'locative'   => 'Республике Башкортостан',
                ],
                'capital'    => [
                    'name'        => 'Уфа',
                    'label'       => 'ufa',
                    'id'          => '0200000100000',
                    'contentType' => 'city',
                ],
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        file_put_contents($citiesPath, json_encode([
            [
                'name'          => 'Ишимбай',
                'name_alt'      => 'Ишимбай',
                'label'         => 'ishimbay',
                'type'          => 'Город',
                'typeShort'     => 'г',
                'contentType'   => 'city',
                'id'            => '0202600100000',
                'okato'         => '80420000000',
                'oktmo'         => '80631101001',
                'guid'          => 'b3d2e10f-752d-4b90-b54b-9d6545f38ae0',
                'isDualName'    => false,
                'isCapital'     => false,
                'zip'           => 453201,
                'population'    => $cityPopulation,
                'yearFounded'   => 1932,
                'yearCityStatus'=> 1940,
                'name_en'       => 'Ishimbay',
                'namecase'      => [
                    'nominative' => 'Ишимбай',
                    'locative'   => 'Ишимбае',
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
                    'id'    => '0200000000000',
                    'name'  => 'Башкортостан',
                    'label' => 'bashkortostan',
                ],
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return [$regionsPath, $citiesPath];
    }
}
