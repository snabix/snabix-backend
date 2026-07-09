<?php

declare(strict_types=1);

namespace App\Location\Application\UseCases\ListRegions;

use App\Location\Infrastructure\Models\EloquentRegion;
use App\Shared\Application\Support\ReferenceDataCache;

class ListRegionsHandler
{
    public function __construct(
        private readonly ReferenceDataCache $cache,
    ) {}

    public function execute(ListRegionsInput $input): ListRegionsOutput
    {
        $regions = $this->cache->rememberLocation(
            'location:regions',
            fn(): array => EloquentRegion::query()
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
                ->all(),
        );

        return new ListRegionsOutput(array_values($regions));
    }
}
