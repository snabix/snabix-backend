<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUserAddress;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ListingAddressSnapshotService
{
    /**
     * @param array<string, mixed> $input
     *
     * @return array{
     *     profile_address_id: string|null,
     *     region_id: int|null,
     *     city_id: int|null,
     *     address_snapshot: array<string, mixed>|null
     * }
     */
    public function resolve(
        string $userId,
        array  $input,
    ): array {
        $mode = $this->normalizeMode(Arr::get($input, 'addressMode', 'none'));

        return match ($mode) {
            'profile' => $this->fromProfileAddress($userId, Arr::get($input, 'profileAddressId')),
            'custom'  => $this->fromCustomAddress($input),
            default   => $this->emptyAddress(),
        };
    }

    /**
     * @return array{
     *     profile_address_id: string|null,
     *     region_id: int|null,
     *     city_id: int|null,
     *     address_snapshot: array<string, mixed>|null
     * }
     */
    private function fromProfileAddress(
        string $userId,
        mixed  $profileAddressId,
    ): array {
        if (!is_string($profileAddressId) || !Str::isUuid($profileAddressId)) {
            throw ValidationException::withMessages([
                'profileAddressId' => ['Выберите адрес из профиля.'],
            ]);
        }

        $address = EloquentUserAddress::query()
            ->with(['region', 'city'])
            ->where('user_id', $userId)
            ->whereKey($profileAddressId)
            ->first();

        if (!$address instanceof EloquentUserAddress) {
            throw ValidationException::withMessages([
                'profileAddressId' => ['Адрес профиля не найден.'],
            ]);
        }

        return [
            'profile_address_id' => $address->id,
            'region_id'          => $address->region_id,
            'city_id'            => $address->city_id,
            'address_snapshot'   => $this->snapshot(
                source: 'profile',
                profileAddressId: $address->id,
                label: $address->label,
                region: $address->region,
                city: $address->city,
                addressLine: $address->address_line,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array{
     *     profile_address_id: string|null,
     *     region_id: int|null,
     *     city_id: int|null,
     *     address_snapshot: array<string, mixed>|null
     * }
     */
    private function fromCustomAddress(
        array $input,
    ): array {
        $regionId = $this->integerValue(Arr::get($input, 'regionId'));
        $cityId   = $this->nullableIntegerValue(Arr::get($input, 'cityId'));

        $region   = EloquentRegion::query()
            ->whereKey($regionId)
            ->where('is_active', true)
            ->first();

        if (!$region instanceof EloquentRegion) {
            throw ValidationException::withMessages([
                'regionId' => ['Регион не найден.'],
            ]);
        }

        $city     = null;

        if ($cityId !== null) {
            $city = EloquentCity::query()
                ->whereKey($cityId)
                ->where('region_id', $region->id)
                ->where('is_active', true)
                ->first();

            if (!$city instanceof EloquentCity) {
                throw ValidationException::withMessages([
                    'cityId' => ['Город не найден в выбранном регионе.'],
                ]);
            }
        }

        return [
            'profile_address_id' => null,
            'region_id'          => $region->id,
            'city_id'            => $city?->id,
            'address_snapshot'   => $this->snapshot(
                source: 'custom',
                profileAddressId: null,
                label: null,
                region: $region,
                city: $city,
                addressLine: $this->nullableString(Arr::get($input, 'addressLine')),
            ),
        ];
    }

    /**
     * @return array{
     *     profile_address_id: string|null,
     *     region_id: int|null,
     *     city_id: int|null,
     *     address_snapshot: array<string, mixed>|null
     * }
     */
    private function emptyAddress(): array
    {
        return [
            'profile_address_id' => null,
            'region_id'          => null,
            'city_id'            => null,
            'address_snapshot'   => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        string         $source,
        ?string        $profileAddressId,
        ?string        $label,
        EloquentRegion $region,
        ?EloquentCity  $city,
        ?string        $addressLine,
    ): array {
        $parts = array_values(
            array_filter(
                [
                    $city?->name,
                    $addressLine,
                ],
                fn(?string $value): bool => $value !== null && $value !== '',
            ),
        );

        return [
            'source'           => $source,
            'profileAddressId' => $profileAddressId,
            'label'            => $label,
            'region'           => [
                'id'       => $region->id,
                'name'     => $region->name,
                'fullName' => $region->fullname ?? $region->name,
                'label'    => $region->label,
            ],
            'city'             => $city === null
                ? null
                : [
                    'id'    => $city->id,
                    'name'  => $city->name,
                    'label' => $city->label,
                    'lat'   => $city->lat,
                    'lon'   => $city->lon,
                ],
            'addressLine'      => $addressLine,
            'display'          => $parts === [] ? $region->name : implode(', ', $parts),
            'coordinates'      => [
                'lat' => null,
                'lng' => null,
            ],
            'mapProvider'      => null,
            'mapPlaceId'       => null,
        ];
    }

    private function normalizeMode(mixed $mode): string
    {
        return is_string($mode) && in_array($mode, ['profile', 'custom', 'none'], true)
            ? $mode
            : 'none';
    }

    private function integerValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        throw ValidationException::withMessages([
            'regionId' => ['Укажите регион объявления.'],
        ]);
    }

    private function nullableIntegerValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : Str::limit($value, 255, '');
    }
}
