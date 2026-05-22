<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListRegionsResponse extends JsonResource
{
    /**
     * @return array{regions: list<array<array-key, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'regions' => $this->regions(),
        ];
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function regions(): array
    {
        if (! is_array($this->resource)) {
            return [];
        }

        return array_values(array_filter(
            $this->resource,
            fn(mixed $region): bool => is_array($region),
        ));
    }
}
