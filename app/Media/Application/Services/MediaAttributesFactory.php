<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Data\StoredMediaFile;
use App\Media\Application\Support\MediaTypeDetector;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Str;

class MediaAttributesFactory
{
    public function __construct(
        private readonly MediaTypeDetector $typeDetector,
    ) {}

    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function forCreate(StoredMediaFile $source, array $attributes): array
    {
        $fileName   = $this->normalizeFileName(basename($source->path));
        $targetDisk = $this->resolveDisk($attributes);

        return [
            'model_type'            => $attributes['model_type'] ?? null,
            'model_id'              => $attributes['model_id'] ?? null,
            'collection_name'       => $attributes['collection_name'] ?? 'default',
            'name'                  => $this->resolveName($attributes['name'] ?? null, $fileName),
            'file_name'             => $fileName,
            'mime_type'             => $source->mimeType,
            'disk'                  => $targetDisk,
            'conversions_disk'      => $targetDisk,
            'size'                  => $source->size,
            'manipulations'         => [],
            'custom_properties'     => [],
            'generated_conversions' => [],
            'responsive_images'     => [],
            'media_type'            => $this->resolveMediaType(
                $attributes['media_type'] ?? null,
                $source->mimeType,
                $fileName,
            ),
            'visibility'            => $attributes['visibility'] ?? MediaVisibility::PUBLIC,
            'uploaded_by_admin_id'  => $attributes['uploaded_by_admin_id'] ?? null,
            'description'           => $attributes['description'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function forReplacement(
        EloquentMedia $media,
        StoredMediaFile $source,
        array $attributes,
    ): array {
        $fileName   = $this->normalizeFileName(basename($source->path));
        $targetDisk = array_key_exists('disk', $attributes) || array_key_exists('visibility', $attributes)
            ? $this->resolveDisk($attributes)
            : $media->disk;

        return [
            'model_type'            => $attributes['model_type'] ?? $media->model_type,
            'model_id'              => $attributes['model_id'] ?? $media->model_id,
            'collection_name'       => $attributes['collection_name'] ?? $media->collection_name,
            'name'                  => $attributes['name'] ?? $media->name,
            'file_name'             => $fileName,
            'mime_type'             => $source->mimeType,
            'disk'                  => $targetDisk,
            'conversions_disk'      => $targetDisk,
            'size'                  => $source->size,
            'media_type'            => $this->resolveMediaType(
                $attributes['media_type'] ?? null,
                $source->mimeType,
                $fileName,
            ),
            'visibility'            => $attributes['visibility'] ?? $media->visibility,
            'uploaded_by_admin_id'  => $attributes['uploaded_by_admin_id'] ?? $media->uploaded_by_admin_id,
            'description'           => $attributes['description'] ?? $media->description,
            'generated_conversions' => [],
            'responsive_images'     => [],
        ];
    }

    /**
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    public function normalizeMetadata(array $attributes): array
    {
        if (isset($attributes['file_name']) && is_string($attributes['file_name'])) {
            $attributes['file_name'] = $this->normalizeFileName($attributes['file_name']);
        }

        if (isset($attributes['disk']) && is_string($attributes['disk'])) {
            $attributes['conversions_disk'] = $attributes['disk'];
        }

        return $attributes;
    }

    public function locationChanged(EloquentMedia $current, EloquentMedia $candidate): bool
    {
        return $current->disk !== $candidate->disk
            || $current->file_name !== $candidate->file_name
            || $current->collection_name !== $candidate->collection_name
            || $current->media_type !== $candidate->media_type;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function resolveDisk(array $attributes): string
    {
        $visibility = $attributes['visibility'] ?? MediaVisibility::PUBLIC;

        if (! $visibility instanceof MediaVisibility) {
            $visibility = is_int($visibility) || is_string($visibility) && is_numeric($visibility)
                ? MediaVisibility::from((int) $visibility)
                : MediaVisibility::PUBLIC;
        }

        $disk       = $attributes['disk'] ?? null;

        return is_string($disk) && $disk !== ''
            ? $disk
            : $visibility->disk();
    }

    private function resolveMediaType(mixed $mediaType, ?string $mimeType, string $fileName): MediaType
    {
        if ($mediaType instanceof MediaType) {
            return $mediaType;
        }

        if (is_numeric($mediaType)) {
            return MediaType::from((int) $mediaType);
        }

        return $this->typeDetector->detect($mimeType, pathinfo($fileName, PATHINFO_EXTENSION) ?: null);
    }

    private function resolveName(mixed $name, string $fileName): string
    {
        return is_string($name) && $name !== ''
            ? $name
            : pathinfo($fileName, PATHINFO_FILENAME);
    }

    private function normalizeFileName(string $fileName): string
    {
        $extension      = pathinfo($fileName, PATHINFO_EXTENSION);
        $name           = pathinfo($fileName, PATHINFO_FILENAME);
        $normalizedName = Str::slug($name) ?: 'media-file';

        return $extension !== ''
            ? $normalizedName . '.' . strtolower($extension)
            : $normalizedName;
    }
}
