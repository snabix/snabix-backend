<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Support;

use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/responsive-images/';
    }

    private function getBasePath(Media $media): string
    {
        $storedBasePath = $this->storedBasePath($media);

        if ($storedBasePath !== null) {
            return $storedBasePath;
        }

        $prefix         = config('media-library.prefix', '');
        $prefix         = trim(is_string($prefix) ? $prefix : '', '/');
        $directory      = $this->resolveDirectory($media);
        $collection     = Str::slug($media->collection_name ?: 'default');
        $mediaIdentity  = $this->resolveMediaIdentity($media);
        $path           = $directory . '/' . $collection . '/' . $mediaIdentity;

        return $prefix !== ''
            ? $prefix . '/' . $path
            : $path;
    }

    private function storedBasePath(Media $media): ?string
    {
        if (! $media instanceof EloquentMedia) {
            return null;
        }

        $storageKey = $media->storage_key;

        if (! is_string($storageKey) || ! str_contains($storageKey, '/')) {
            return null;
        }

        return substr($storageKey, 0, (int) strrpos($storageKey, '/'));
    }

    private function resolveMediaIdentity(Media $media): string
    {
        $uuid     = $media->uuid;

        if ($uuid !== '') {
            return $uuid;
        }

        $mediaKey = $media->getKey();

        return is_string($mediaKey) || is_int($mediaKey)
            ? (string) $mediaKey
            : 'media';
    }

    private function resolveDirectory(Media $media): string
    {
        if ($media instanceof EloquentMedia) {
            $mediaType = $media->getAttribute('media_type');

            if ($mediaType instanceof MediaType) {
                return $mediaType->directory();
            }
        }

        return MediaType::FILE->directory();
    }
}
