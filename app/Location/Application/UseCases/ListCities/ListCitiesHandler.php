<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListCities;

use App\Location\Infrastructure\Models\EloquentCity;
use App\Shared\Application\Support\ReferenceDataCache;

class ListCitiesHandler
{
    public function __construct(
        private readonly ReferenceDataCache $cache,
    ) {}

    public function execute(ListCitiesInput $input): ListCitiesOutput
    {
        $search = $input->search !== null ? trim($input->search) : '';
        $cities = $this->cache->rememberLocation(
            'location:cities:region:' . $input->regionId . ':search:' . md5(mb_strtolower($search)),
            fn(): array => EloquentCity::query()
                ->where('is_active', true)
                ->where('region_id', $input->regionId)
                ->when(
                    $search !== '',
                    fn($query) => $query->where('name', 'ilike', '%' . $search . '%'),
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
                ->all(),
        );

        return new ListCitiesOutput(array_values($cities));
    }
}
