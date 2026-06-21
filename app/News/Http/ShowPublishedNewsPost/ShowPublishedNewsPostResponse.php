<?php

declare(strict_types=1);

namespace App\News\Http\ShowPublishedNewsPost;

use App\News\Application\UseCases\ShowPublishedNewsPost\ShowPublishedNewsPostOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ShowPublishedNewsPostOutput
 */
class ShowPublishedNewsPostResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
