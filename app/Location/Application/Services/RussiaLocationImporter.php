<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use Throwable;

class RussiaLocationImporter
{
    /**
     * @return array{regions_created: int, regions_updated: int, cities_created: int, cities_updated: int}
     *
     * @throws Throwable
     */
    public function import(
        string $regionsPath,
        string $citiesPath,
        bool $fresh = false,
    ): array {
        $regions = $this->readJsonFile($regionsPath);
        $cities  = $this->readJsonFile($citiesPath);

        return DB::transaction(function () use ($regions, $cities, $fresh): array {
            if ($fresh) {
                EloquentCity::query()->delete();
                EloquentRegion::query()->delete();
            }

            $stats           = [
                'regions_created' => 0,
                'regions_updated' => 0,
                'cities_created'  => 0,
                'cities_updated'  => 0,
            ];

            $regionByKladrId = [];

            foreach ($regions as $index => $payload) {
                $region                             = $this->persistRegion($payload, $index, $stats);
                $regionByKladrId[$region->kladr_id] = $region;
            }

            foreach ($cities as $index => $payload) {
                $regionKladrId = $this->nullableString(Arr::get($payload, 'region.id'));

                if ($regionKladrId === null || ! isset($regionByKladrId[$regionKladrId])) {
                    throw new InvalidArgumentException(sprintf(
                        'Не найден регион [%s] для города [%s].',
                        $regionKladrId ?? 'null',
                        $this->nullableString(Arr::get($payload, 'name')) ?? '#' . $index,
                    ));
                }

                $this->persistCity($payload, $regionByKladrId[$regionKladrId], $index, $stats);
            }

            return $stats;
        });
    }

    /**
     * @return array{regions: int, cities: int}
     * @throws JsonException
     */
    public function preview(
        string $regionsPath,
        string $citiesPath,
    ): array {
        return [
            'regions' => count($this->readJsonFile($regionsPath)),
            'cities'  => count($this->readJsonFile($citiesPath)),
        ];
    }

    /**
     * @param array<string, mixed>                                                                        $payload
     * @param array{regions_created: int, regions_updated: int, cities_created: int, cities_updated: int} $stats
     */
    private function persistRegion(array $payload, int $sortOrder, array &$stats): EloquentRegion
    {
        $kladrId = $this->requiredString($payload, 'id');
        $region  = EloquentRegion::query()->firstOrNew(['kladr_id' => $kladrId]);
        $exists  = $region->exists;

        $region->fill([
            'fias_guid'      => $this->nullableString(Arr::get($payload, 'guid')),
            'name'           => $this->requiredString($payload, 'name'),
            'slug'           => $this->slug($payload),
            'label'          => $this->requiredString($payload, 'label'),
            'type'           => $this->nullableString(Arr::get($payload, 'type')),
            'type_short'     => $this->nullableString(Arr::get($payload, 'typeShort')),
            'content_type'   => $this->nullableString(Arr::get($payload, 'contentType')) ?? 'region',
            'okato'          => $this->nullableString(Arr::get($payload, 'okato')),
            'oktmo'          => $this->nullableString(Arr::get($payload, 'oktmo')),
            'code'           => $this->nullableString(Arr::get($payload, 'code')),
            'iso_code'       => $this->nullableString(Arr::get($payload, 'iso_3166-2')),
            'population'     => $this->nullableInteger(Arr::get($payload, 'population')),
            'year_founded'   => $this->nullableInteger(Arr::get($payload, 'yearFounded')),
            'area'           => $this->nullableInteger(Arr::get($payload, 'area')),
            'fullname'       => $this->nullableString(Arr::get($payload, 'fullname')),
            'unofficial_name'=> $this->nullableString(Arr::get($payload, 'unofficialName')),
            'name_en'        => $this->nullableString(Arr::get($payload, 'name_en')),
            'district'       => $this->nullableString(Arr::get($payload, 'district')),
            'name_cases'     => $this->nullableArray(Arr::get($payload, 'namecase')),
            'capital_data'   => $this->nullableArray(Arr::get($payload, 'capital')),
            'is_active'      => true,
            'sort_order'     => $sortOrder,
        ]);
        $region->save();

        $stats[$exists ? 'regions_updated' : 'regions_created']++;

        return $region;
    }

    /**
     * @param array<string, mixed>                                                                        $payload
     * @param array{regions_created: int, regions_updated: int, cities_created: int, cities_updated: int} $stats
     */
    private function persistCity(
        array $payload,
        EloquentRegion $region,
        int $sortOrder,
        array &$stats,
    ): EloquentCity {
        $kladrId = $this->requiredString($payload, 'id');
        $city    = EloquentCity::query()->firstOrNew(['kladr_id' => $kladrId]);
        $exists  = $city->exists;

        $city->fill([
            'region_id'       => $region->id,
            'fias_guid'       => $this->nullableString(Arr::get($payload, 'guid')),
            'name'            => $this->requiredString($payload, 'name'),
            'name_alt'        => $this->nullableString(Arr::get($payload, 'name_alt')),
            'slug'            => $this->slug($payload),
            'label'           => $this->requiredString($payload, 'label'),
            'type'            => $this->nullableString(Arr::get($payload, 'type')),
            'type_short'      => $this->nullableString(Arr::get($payload, 'typeShort')),
            'content_type'    => $this->nullableString(Arr::get($payload, 'contentType')) ?? 'city',
            'okato'           => $this->nullableString(Arr::get($payload, 'okato')),
            'oktmo'           => $this->nullableString(Arr::get($payload, 'oktmo')),
            'zip'             => $this->nullableInteger(Arr::get($payload, 'zip')),
            'population'      => $this->nullableInteger(Arr::get($payload, 'population')),
            'year_founded'    => $this->nullableString(Arr::get($payload, 'yearFounded')),
            'year_city_status'=> $this->nullableString(Arr::get($payload, 'yearCityStatus')),
            'name_en'         => $this->nullableString(Arr::get($payload, 'name_en')),
            'name_cases'      => $this->nullableArray(Arr::get($payload, 'namecase')),
            'lat'             => $this->nullableString(Arr::get($payload, 'coords.lat')),
            'lon'             => $this->nullableString(Arr::get($payload, 'coords.lon')),
            'timezone'        => $this->nullableArray(Arr::get($payload, 'timezone')),
            'is_capital'      => (bool) Arr::get($payload, 'isCapital', false),
            'is_dual_name'    => (bool) Arr::get($payload, 'isDualName', false),
            'is_active'       => true,
            'sort_order'      => $sortOrder,
        ]);
        $city->save();

        $stats[$exists ? 'cities_updated' : 'cities_created']++;

        return $city;
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws JsonException
     */
    private function readJsonFile(
        string $path,
    ): array {
        if (! is_file($path)) {
            throw new InvalidArgumentException(sprintf('Файл [%s] не найден.', $path));
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException(sprintf('Не удалось прочитать файл [%s].', $path));
        }

        $decoded  = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException(sprintf('Файл [%s] должен содержать JSON-массив.', $path));
        }

        /** @var array<int, array<string, mixed>> $decoded */
        return $decoded;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(
        array $payload,
        string $key,
    ): string {
        $value = $this->nullableString(Arr::get($payload, $key));

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('Поле [%s] обязательно для импорта локаций.', $key));
        }

        return $value;
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            $normalized = trim((string) $value);

            return $normalized !== '' ? $normalized : null;
        }

        return null;
    }

    private function nullableInteger(
        mixed $value,
    ): ?int {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function nullableArray(
        mixed $value,
    ): ?array {
        if (! is_array($value)) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function slug(
        array $payload,
    ): string {
        $label = $this->nullableString(Arr::get($payload, 'label'));

        if ($label !== null) {
            return str_replace('_', '-', $label);
        }

        return Str::slug($this->requiredString($payload, 'name'));
    }
}
