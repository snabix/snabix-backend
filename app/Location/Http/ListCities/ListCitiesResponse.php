<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListCitiesResponse extends JsonResource
{
    /**
     * @return array{cities: list<array<array-key, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'cities' => $this->cities(),
        ];
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function cities(): array
    {
        if (! is_array($this->resource)) {
            return [];
        }

        return array_values(array_filter(
            $this->resource,
            fn(mixed $city): bool => is_array($city),
        ));
    }
}
