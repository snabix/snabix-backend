<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;

class RussiaLocationPayloadNormalizer
{
    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function region(array $payload, int $sortOrder): array
    {
        return [
            'kladr_id'        => $this->requiredString($payload, 'id'),
            'fias_guid'       => $this->nullableString(Arr::get($payload, 'guid')),
            'name'            => $this->requiredString($payload, 'name'),
            'slug'            => $this->slug($payload),
            'label'           => $this->requiredString($payload, 'label'),
            'type'            => $this->nullableString(Arr::get($payload, 'type')),
            'type_short'      => $this->nullableString(Arr::get($payload, 'typeShort')),
            'content_type'    => $this->nullableString(Arr::get($payload, 'contentType')) ?? 'region',
            'okato'           => $this->nullableString(Arr::get($payload, 'okato')),
            'oktmo'           => $this->nullableString(Arr::get($payload, 'oktmo')),
            'code'            => $this->nullableString(Arr::get($payload, 'code')),
            'iso_code'        => $this->nullableString(Arr::get($payload, 'iso_3166-2')),
            'population'      => $this->nullableInteger(Arr::get($payload, 'population')),
            'year_founded'    => $this->nullableInteger(Arr::get($payload, 'yearFounded')),
            'area'            => $this->nullableInteger(Arr::get($payload, 'area')),
            'fullname'        => $this->nullableString(Arr::get($payload, 'fullname')),
            'unofficial_name' => $this->nullableString(Arr::get($payload, 'unofficialName')),
            'name_en'         => $this->nullableString(Arr::get($payload, 'name_en')),
            'district'        => $this->nullableString(Arr::get($payload, 'district')),
            'name_cases'      => $this->nullableJson(Arr::get($payload, 'namecase')),
            'capital_data'    => $this->nullableJson(Arr::get($payload, 'capital')),
            'is_active'       => true,
            'sort_order'      => $sortOrder,
        ];
    }

    /**
     * @param  array<string, mixed>                                             $payload
     * @return array{region_kladr_id: string, attributes: array<string, mixed>}
     *
     * @throws JsonException
     */
    public function city(array $payload, int $sortOrder): array
    {
        $regionKladrId = $this->nullableString(Arr::get($payload, 'region.id'));

        if ($regionKladrId === null) {
            throw new InvalidArgumentException(sprintf(
                'Не указан регион для города [%s].',
                $this->nullableString(Arr::get($payload, 'name')) ?? '#' . $sortOrder,
            ));
        }

        return [
            'region_kladr_id' => $regionKladrId,
            'attributes'      => [
                'kladr_id'         => $this->requiredString($payload, 'id'),
                'fias_guid'        => $this->nullableString(Arr::get($payload, 'guid')),
                'name'             => $this->requiredString($payload, 'name'),
                'name_alt'         => $this->nullableString(Arr::get($payload, 'name_alt')),
                'slug'             => $this->slug($payload),
                'label'            => $this->requiredString($payload, 'label'),
                'type'             => $this->nullableString(Arr::get($payload, 'type')),
                'type_short'       => $this->nullableString(Arr::get($payload, 'typeShort')),
                'content_type'     => $this->nullableString(Arr::get($payload, 'contentType')) ?? 'city',
                'okato'            => $this->nullableString(Arr::get($payload, 'okato')),
                'oktmo'            => $this->nullableString(Arr::get($payload, 'oktmo')),
                'zip'              => $this->nullableInteger(Arr::get($payload, 'zip')),
                'population'       => $this->nullableInteger(Arr::get($payload, 'population')),
                'year_founded'     => $this->nullableString(Arr::get($payload, 'yearFounded')),
                'year_city_status' => $this->nullableString(Arr::get($payload, 'yearCityStatus')),
                'name_en'          => $this->nullableString(Arr::get($payload, 'name_en')),
                'name_cases'       => $this->nullableJson(Arr::get($payload, 'namecase')),
                'lat'              => $this->nullableString(Arr::get($payload, 'coords.lat')),
                'lon'              => $this->nullableString(Arr::get($payload, 'coords.lon')),
                'timezone'         => $this->nullableJson(Arr::get($payload, 'timezone')),
                'is_capital'       => (bool) Arr::get($payload, 'isCapital', false),
                'is_dual_name'     => (bool) Arr::get($payload, 'isDualName', false),
                'is_active'        => true,
                'sort_order'       => $sortOrder,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $key): string
    {
        $value = $this->nullableString(Arr::get($payload, $key));

        if ($value === null) {
            throw new InvalidArgumentException(sprintf('Поле [%s] обязательно для импорта локаций.', $key));
        }

        return $value;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            $normalized = trim((string) $value);

            return $normalized !== '' ? $normalized : null;
        }

        return null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @throws JsonException
     */
    private function nullableJson(mixed $value): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function slug(array $payload): string
    {
        $label = $this->nullableString(Arr::get($payload, 'label'));

        if ($label !== null) {
            return str_replace('_', '-', $label);
        }

        return Str::slug($this->requiredString($payload, 'name'));
    }
}
