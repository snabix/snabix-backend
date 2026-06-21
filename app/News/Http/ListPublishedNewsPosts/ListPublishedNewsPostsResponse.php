<?php

declare(strict_types=1);

namespace App\News\Http\ListPublishedNewsPosts;

use App\News\Application\UseCases\ListPublishedNewsPosts\ListPublishedNewsPostsOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ListPublishedNewsPostsOutput
 */
class ListPublishedNewsPostsResponse extends OutputResource
{
    /**
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, int>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
