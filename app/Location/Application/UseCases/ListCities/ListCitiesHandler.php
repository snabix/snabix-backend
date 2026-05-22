<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListCities;

use App\Location\Infrastructure\Models\EloquentCity;

class ListCitiesHandler
{
    public function execute(ListCitiesInput $input): ListCitiesOutput
    {
        $cities = EloquentCity::query()
            ->where('is_active', true)
            ->where('region_id', $input->regionId)
            ->when(
                $input->search !== null,
                fn($query) => $query->where('name', 'ilike', '%' . $input->search . '%'),
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

        return new ListCitiesOutput(array_values($cities));
    }
}
