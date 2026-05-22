<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListRegions;

use App\Location\Infrastructure\Models\EloquentRegion;

class ListRegionsHandler
{
    public function execute(ListRegionsInput $input): ListRegionsOutput
    {
        $regions = EloquentRegion::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn(EloquentRegion $region): array => [
                'id'       => $region->id,
                'name'     => $region->name,
                'fullName' => $region->fullname ?? $region->name,
                'label'    => $region->label,
                'type'     => $region->type,
            ])
            ->values()
            ->all();

        return new ListRegionsOutput(array_values($regions));
    }
}
