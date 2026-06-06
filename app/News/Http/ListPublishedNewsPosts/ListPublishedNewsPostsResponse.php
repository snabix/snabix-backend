<?php

declare(strict_types=1);

namespace App\News\Http\ListPublishedNewsPosts;

use App\News\Application\UseCases\ListPublishedNewsPosts\ListPublishedNewsPostsOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListPublishedNewsPostsOutput
 */
class ListPublishedNewsPostsResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'items' => $this->items,
            'meta'  => $this->meta,
        ];
    }
}
