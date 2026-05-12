<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Support;

use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
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
        $prefix    = config('media-library.prefix', '');
        $prefix    = trim(is_string($prefix) ? $prefix : '', '/');
        $directory = $this->resolveDirectory($media);
        $mediaKey  = $media->getKey();
        $path      = $directory . '/' . (is_string($mediaKey) || is_int($mediaKey) ? (string) $mediaKey : '');

        return $prefix !== ''
            ? $prefix . '/' . $path
            : $path;
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
