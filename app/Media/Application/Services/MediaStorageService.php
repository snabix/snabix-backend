<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Support\MediaTypeDetector;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

readonly class MediaStorageService
{
    public function __construct(
        private MediaTypeDetector $typeDetector,
    ) {}

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function createFromStoredUpload(string $sourceDisk, string $sourcePath, array $attributes): EloquentMedia
    {
        return DB::transaction(function () use ($sourceDisk, $sourcePath, $attributes): EloquentMedia {
            $source     = Storage::disk($sourceDisk);

            if (! $source->exists($sourcePath)) {
                throw new RuntimeException('Uploaded media file was not found.');
            }

            $fileName   = $this->normalizeFileName(basename($sourcePath));
            $mimeType   = $source->mimeType($sourcePath) ?: null;
            $size       = $source->size($sourcePath);
            $targetDisk = $this->resolveDisk($attributes);
            $mediaType  = $this->resolveMediaType($attributes['media_type'] ?? null, $mimeType, $fileName);

            $media      = EloquentMedia::query()->create([
                'model_type'            => $attributes['model_type'] ?? null,
                'model_id'              => $attributes['model_id'] ?? null,
                'collection_name'       => $attributes['collection_name'] ?? 'default',
                'name'                  => $attributes['name'] ?: pathinfo($fileName, PATHINFO_FILENAME),
                'file_name'             => $fileName,
                'mime_type'             => $mimeType,
                'disk'                  => $targetDisk,
                'conversions_disk'      => $targetDisk,
                'size'                  => $size,
                'manipulations'         => [],
                'custom_properties'     => [],
                'generated_conversions' => [],
                'responsive_images'     => [],
                'media_type'            => $mediaType,
                'visibility'            => $attributes['visibility'] ?? MediaVisibility::PUBLIC,
                'uploaded_by_admin_id'  => $attributes['uploaded_by_admin_id'] ?? null,
                'description'           => $attributes['description'] ?? null,
            ]);

            $this->storeMediaFile($media, $sourceDisk, $sourcePath, $fileName);

            return $media->refresh();
        });
    }

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function replaceFromStoredUpload(EloquentMedia $media, string $sourceDisk, string $sourcePath, array $attributes): EloquentMedia
    {
        return DB::transaction(function () use ($media, $sourceDisk, $sourcePath, $attributes): EloquentMedia {
            $source     = Storage::disk($sourceDisk);

            if (! $source->exists($sourcePath)) {
                throw new RuntimeException('Uploaded replacement media file was not found.');
            }

            /** @var Filesystem $filesystem */
            $filesystem = app(Filesystem::class);
            $filesystem->removeAllFiles($media);

            $fileName   = $this->normalizeFileName(basename($sourcePath));
            $mimeType   = $source->mimeType($sourcePath) ?: null;
            $targetDisk = $this->resolveDisk($attributes);

            $media->forceFill([
                'model_type'            => $attributes['model_type'] ?? $media->model_type,
                'model_id'              => $attributes['model_id'] ?? $media->model_id,
                'collection_name'       => $attributes['collection_name'] ?? $media->collection_name,
                'name'                  => $attributes['name'] ?? $media->name,
                'file_name'             => $fileName,
                'mime_type'             => $mimeType,
                'disk'                  => $targetDisk,
                'conversions_disk'      => $targetDisk,
                'size'                  => $source->size($sourcePath),
                'media_type'            => $this->resolveMediaType($attributes['media_type'] ?? null, $mimeType, $fileName),
                'visibility'            => $attributes['visibility'] ?? $media->visibility,
                'uploaded_by_admin_id'  => $attributes['uploaded_by_admin_id'] ?? $media->uploaded_by_admin_id,
                'description'           => $attributes['description'] ?? $media->description,
                'generated_conversions' => [],
                'responsive_images'     => [],
            ])->saveQuietly();

            $this->storeMediaFile($media, $sourceDisk, $sourcePath, $fileName);

            return $media->refresh();
        });
    }

    /**
     * @param  array<string, mixed> $attributes
     * @throws Throwable
     */
    public function updateMetadata(EloquentMedia $media, array $attributes): EloquentMedia
    {
        return DB::transaction(function () use ($media, $attributes): EloquentMedia {
            $oldDisk = $media->disk;
            $oldPath = PathGeneratorFactory::create($media)->getPath($media) . $media->file_name;

            $media->update($attributes);

            $newPath = PathGeneratorFactory::create($media)->getPath($media) . $media->file_name;

            if ($oldDisk === $media->disk && $oldPath === $newPath) {
                return $media->refresh();
            }

            $stream  = Storage::disk($oldDisk)->readStream($oldPath);

            if (! is_resource($stream)) {
                throw new RuntimeException('Unable to read existing media file.');
            }

            Storage::disk($media->disk)->put($newPath, $stream);

            fclose($stream);

            Storage::disk($oldDisk)->delete($oldPath);

            return $media->refresh();
        });
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

    private function storeMediaFile(EloquentMedia $media, string $sourceDisk, string $sourcePath, string $fileName): void
    {
        $stream        = Storage::disk($sourceDisk)->readStream($sourcePath);

        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to read uploaded media file.');
        }

        $pathGenerator = PathGeneratorFactory::create($media);

        Storage::disk($media->disk)->put($pathGenerator->getPath($media) . $fileName, $stream);

        fclose($stream);

        Storage::disk($sourceDisk)->delete($sourcePath);
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
