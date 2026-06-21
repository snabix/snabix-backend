<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use App\Location\Application\UseCases\ListCities\ListCitiesOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListCitiesOutput
 */
class ListCitiesResponse extends OutputResource
{
    /**
     * @return array{cities: list<array<string, mixed>>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
