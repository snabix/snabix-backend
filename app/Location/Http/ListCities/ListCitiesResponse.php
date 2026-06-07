<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use App\Location\Application\UseCases\ListCities\ListCitiesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListCitiesOutput
 */
class ListCitiesResponse extends JsonResource
{
    /**
     * @return array{cities: list<array<string, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'cities' => $this->cities,
        ];
    }
}
