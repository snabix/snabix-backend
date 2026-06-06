<?php

declare(strict_types=1);

namespace App\News\Http\ShowPublishedNewsPost;

use App\News\Application\UseCases\ShowPublishedNewsPost\ShowPublishedNewsPostOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShowPublishedNewsPostOutput
 */
class ShowPublishedNewsPostResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
