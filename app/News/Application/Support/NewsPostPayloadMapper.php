<?php

declare(strict_types=1);

namespace App\News\Application\Support;

use App\News\Infrastructure\Models\EloquentNewsPost;
use App\News\Infrastructure\Models\EloquentNewsPostBlock;

class NewsPostPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(EloquentNewsPost $post, bool $includeBlocks = false): array
    {
        $payload = [
            'id'             => $post->id,
            'status'         => $post->status->value,
            'statusLabel'    => $post->status->label(),
            'title'          => $post->title,
            'slug'           => $post->slug,
            'category'       => $post->category,
            'eyebrow'        => $post->eyebrow,
            'description'    => $post->description,
            'thesis'         => $post->thesis,
            'readingTime'    => $post->reading_time,
            'isFeatured'     => $post->is_featured,
            'viewsCount'     => $post->views_count,
            'imageUrl'       => $post->coverMedia?->getFullUrl(),
            'coverMedia'     => $post->coverMedia === null
                ? null
                : [
                    'id'       => $post->coverMedia->id,
                    'url'      => $post->coverMedia->getFullUrl(),
                    'fileName' => $post->coverMedia->file_name,
                    'mimeType' => $post->coverMedia->mime_type,
                ],
            'author'         => $post->authorAdmin === null
                ? null
                : [
                    'id'    => $post->authorAdmin->id,
                    'name'  => $post->authorAdmin->name,
                    'email' => $post->authorAdmin->email,
                ],
            'publishedAt'    => $post->published_at?->toIso8601String(),
            'createdAt'      => $post->created_at?->toIso8601String(),
            'updatedAt'      => $post->updated_at?->toIso8601String(),
        ];

        if ($includeBlocks) {
            $payload['contentBlocks'] = $post->blocks
                ->map(fn(EloquentNewsPostBlock $block): array => $this->mapBlock($block))
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBlock(EloquentNewsPostBlock $block): array
    {
        $data = $block->data;

        if ($block->media !== null) {
            $data['media'] = [
                'id'       => $block->media->id,
                'url'      => $block->media->getFullUrl(),
                'fileName' => $block->media->file_name,
                'mimeType' => $block->media->mime_type,
            ];

            $data['imageUrl'] ??= $block->media->getFullUrl();
        }

        return [
            'id'        => $block->id,
            'type'      => $block->type->apiName(),
            'typeValue' => $block->type->value,
            'typeLabel' => $block->type->label(),
            'sortOrder' => $block->sort_order,
            ...$data,
        ];
    }
}
