<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUserAddress;
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserAddressService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listPayload(string $userId): array
    {
        $addresses = EloquentUserAddress::query()
            ->with(['region', 'city'])
            ->where('user_id', $userId)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->map(fn(EloquentUserAddress $address): array => $this->toPayload($address))
            ->values()
            ->all();

        return array_values($addresses);
    }

    /**
     * @param list<array<array-key, mixed>> $items
     *
     * @return list<array<string, mixed>>
     *
     * @throws ValidationException|Throwable
     */
    public function replace(string $userId, array $items): array
    {
        return DB::transaction(function () use ($userId, $items): array {
            $primaryIndex = $this->resolvePrimaryIndex($items);
            $keptIds      = [];

            foreach ($items as $index => $item) {
                $regionId = $this->integerValue(Arr::get($item, 'regionId'));
                $cityId   = $this->nullableIntegerValue(Arr::get($item, 'cityId'));

                $this->ensureLocationIsValid($regionId, $cityId, $index);

                $id       = Arr::get($item, 'id');
                $address  = is_string($id) && $id !== ''
                    ? EloquentUserAddress::query()
                        ->where('user_id', $userId)
                        ->whereKey($id)
                        ->first()
                    : new EloquentUserAddress();

                if ($address === null) {
                    throw ValidationException::withMessages([
                        "addresses.$index.id" => ['Адрес не найден.'],
                    ]);
                }

                $address->fill([
                    'user_id'      => $userId,
                    'region_id'    => $regionId,
                    'city_id'      => $cityId,
                    'label'        => $this->nullableStringValue(Arr::get($item, 'label')),
                    'address_line' => $this->nullableStringValue(Arr::get($item, 'addressLine')),
                    'is_primary'   => $index === $primaryIndex,
                    'sort_order'   => $index,
                ]);
                $address->save();

                $key      = $address->getKey();

                if (is_string($key)) {
                    $keptIds[] = $key;
                }
            }

            EloquentUserAddress::query()
                ->where('user_id', $userId)
                ->when($keptIds !== [], fn($query) => $query->whereKeyNot($keptIds))
                ->delete();

            return $this->listPayload($userId);
        });
    }

    public function delete(string $userId, string $addressId): void
    {
        EloquentUserAddress::query()
            ->where('user_id', $userId)
            ->whereKey($addressId)
            ->delete();

        $hasPrimary  = EloquentUserAddress::query()
            ->where('user_id', $userId)
            ->where('is_primary', true)
            ->exists();

        if ($hasPrimary) {
            return;
        }

        $nextPrimary = EloquentUserAddress::query()
            ->where('user_id', $userId)
            ->orderBy('sort_order')
            ->first();

        $nextPrimary?->forceFill(['is_primary' => true])->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(EloquentUserAddress $address): array
    {
        return [
            'id'          => $address->id,
            'label'       => $address->label,
            'addressLine' => $address->address_line,
            'isPrimary'   => $address->is_primary,
            'region'      => [
                'id'       => $address->region->id,
                'name'     => $address->region->name,
                'fullName' => $address->region->fullname ?? $address->region->name,
                'label'    => $address->region->label,
            ],
            'city'        => $address->city !== null
                ? [
                    'id'    => $address->city->id,
                    'name'  => $address->city->name,
                    'label' => $address->city->label,
                ]
                : null,
        ];
    }

    /**
     * @param list<array<array-key, mixed>> $items
     */
    private function resolvePrimaryIndex(array $items): int
    {
        foreach ($items as $index => $item) {
            if ((bool) Arr::get($item, 'isPrimary', false)) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @throws ValidationException
     */
    private function ensureLocationIsValid(int $regionId, ?int $cityId, int $index): void
    {
        if (! EloquentRegion::query()->whereKey($regionId)->exists()) {
            throw ValidationException::withMessages([
                "addresses.$index.regionId" => ['Регион не найден.'],
            ]);
        }

        if ($cityId === null) {
            return;
        }

        if (! EloquentCity::query()->whereKey($cityId)->where('region_id', $regionId)->exists()) {
            throw ValidationException::withMessages([
                "addresses.$index.cityId" => ['Город не относится к выбранному региону.'],
            ]);
        }
    }

    private function integerValue(mixed $value): int
    {
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : 0);
    }

    private function nullableIntegerValue(mixed $value): ?int
    {
        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    private function nullableStringValue(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
