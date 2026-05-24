<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use App\Location\Application\UseCases\ListRegions\ListRegionsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListRegionsOutput
 */
class ListRegionsResponse extends JsonResource
{
    /**
     * @return array{regions: list<array<string, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'regions' => $this->regions,
        ];
    }
}
