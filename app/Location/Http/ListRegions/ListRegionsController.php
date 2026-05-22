<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use App\Location\Infrastructure\Models\EloquentRegion;

class ListRegionsController
{
    public function __invoke(): ListRegionsResponse
    {
        $regions = EloquentRegion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn(EloquentRegion $region): array => [
                'id'    => $region->id,
                'name'  => $region->name,
                'label' => $region->label,
                'type'  => $region->type,
            ])
            ->values()
            ->all();

        return ListRegionsResponse::make($regions);
    }
}
