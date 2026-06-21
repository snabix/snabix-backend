<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use App\Location\Application\UseCases\ListRegions\ListRegionsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListRegionsOutput
 */
class ListRegionsResponse extends OutputResource
{
    /**
     * @return array{regions: list<array<string, mixed>>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
