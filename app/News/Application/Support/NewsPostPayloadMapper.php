<?php

declare(strict_types=1);

namespace App\News\Application\Support;

use App\News\Domain\Enums\NewsPostBlockType;
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
        return [
            'id'        => $block->id,
            'type'      => $block->type->apiName(),
            'typeValue' => $block->type->value,
            'typeLabel' => $block->type->label(),
            'sortOrder' => $block->sort_order,
            ...$this->mapBlockData($block),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBlockData(EloquentNewsPostBlock $block): array
    {
        $data = is_array($block->data) ? $block->data : [];

        return match ($block->type) {
            NewsPostBlockType::LEAD,
            NewsPostBlockType::PARAGRAPH  => [
                'text' => $this->stringValue($data['text'] ?? null),
            ],
            NewsPostBlockType::QUOTE      => [
                'text'   => $this->stringValue($data['text'] ?? null),
                'author' => $this->optionalStringValue($data['author'] ?? null),
            ],
            NewsPostBlockType::SPLIT,
            NewsPostBlockType::STEPS      => [
                'items' => $this->textItems($data['items'] ?? null),
            ],
            NewsPostBlockType::METRICS    => [
                'items' => $this->metricItems($data['items'] ?? null),
            ],
            NewsPostBlockType::IMAGE      => $this->imageBlockData($block, $data),
            NewsPostBlockType::GALLERY    => [
                'items' => $this->imageItems($data['items'] ?? null),
            ],
            NewsPostBlockType::TABLE      => [
                'columns' => $this->stringList($data['columns'] ?? null),
                'rows'    => $this->tableRows($data['rows'] ?? null),
            ],
            NewsPostBlockType::IMAGE_GRID => [
                'items' => $this->imageGridItems($data['items'] ?? null),
            ],
            NewsPostBlockType::CTA        => [
                'title'       => $this->optionalStringValue($data['title'] ?? null),
                'text'        => $this->optionalStringValue($data['text'] ?? null),
                'buttonLabel' => $this->optionalStringValue($data['buttonLabel'] ?? null),
                'href'        => $this->optionalStringValue($data['href'] ?? null),
            ],
        };
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function imageBlockData(EloquentNewsPostBlock $block, array $data): array
    {
        $payload = [
            'caption'  => $this->optionalStringValue($data['caption'] ?? null),
            'imageUrl' => $this->optionalStringValue($data['imageUrl'] ?? null),
        ];

        if ($block->blockMedia !== null) {
            $payload['media'] = [
                'id'       => $block->blockMedia->id,
                'url'      => $block->blockMedia->getFullUrl(),
                'fileName' => $block->blockMedia->file_name,
                'mimeType' => $block->blockMedia->mime_type,
            ];

            $payload['imageUrl'] ??= $block->blockMedia->getFullUrl();
        }

        return array_filter($payload, fn(mixed $value): bool => $value !== null);
    }

    /**
     * @return array<int, array{title: string, text: string}>
     */
    private function textItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            fn(mixed $item): array => [
                'title' => $this->stringValue(is_array($item) ? ($item['title'] ?? null) : null),
                'text'  => $this->stringValue(is_array($item) ? ($item['text'] ?? null) : null),
            ],
            $items,
        ));
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private function metricItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            fn(mixed $item): array => [
                'label' => $this->stringValue(is_array($item) ? ($item['label'] ?? null) : null),
                'value' => $this->stringValue(is_array($item) ? ($item['value'] ?? null) : null),
            ],
            $items,
        ));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function imageItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            function (mixed $item): array {
                if (! is_array($item)) {
                    return [];
                }

                return array_filter([
                    'caption'  => $this->optionalStringValue($item['caption'] ?? null),
                    'imageUrl' => $this->optionalStringValue($item['imageUrl'] ?? null),
                ], fn(mixed $value): bool => $value !== null);
            },
            $items,
        ));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function imageGridItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            function (mixed $item): array {
                if (! is_array($item)) {
                    return [];
                }

                return array_filter([
                    'title'    => $this->optionalStringValue($item['title'] ?? null),
                    'text'     => $this->optionalStringValue($item['text'] ?? null),
                    'caption'  => $this->optionalStringValue($item['caption'] ?? null),
                    'imageUrl' => $this->optionalStringValue($item['imageUrl'] ?? null),
                ], fn(mixed $value): bool => $value !== null);
            },
            $items,
        ));
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map($this->stringValue(...), $items));
    }

    /**
     * @return array<int, array<int, string|int|float|bool|null>>
     */
    private function tableRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return array_values(array_map(
            fn(mixed $row): array => is_array($row)
                ? array_values(array_map($this->tableCellValue(...), $row))
                : [],
            $rows,
        ));
    }

    private function tableCellValue(mixed $value): string | int | float | bool | null
    {
        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value) || $value === null) {
            return $value;
        }

        return (string) $value;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function optionalStringValue(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }
}
