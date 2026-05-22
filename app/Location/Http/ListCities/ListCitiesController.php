<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use App\Location\Infrastructure\Models\EloquentCity;

class ListCitiesController
{
    public function __invoke(ListCitiesRequest $request): ListCitiesResponse
    {
        $cities = EloquentCity::query()
            ->where('is_active', true)
            ->where('region_id', $request->regionId())
            ->when(
                $request->search() !== null,
                fn($query) => $query->where('name', 'ilike', '%' . $request->search() . '%'),
            )
            ->orderByDesc('is_capital')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(100)
            ->get()
            ->map(fn(EloquentCity $city): array => [
                'id'       => $city->id,
                'regionId' => $city->region_id,
                'name'     => $city->name,
                'label'    => $city->label,
                'type'     => $city->type,
            ])
            ->values()
            ->all();

        return ListCitiesResponse::make($cities);
    }
}
