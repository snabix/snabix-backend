<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Infrastructure\Models\EloquentMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class MediaStoragePathResolver
{
    public function originalPath(EloquentMedia $media): string
    {
        $storageKey = $media->storage_key;

        if (is_string($storageKey) && $storageKey !== '') {
            return $storageKey;
        }

        return PathGeneratorFactory::create($media)->getPath($media) . $media->file_name;
    }

    /**
     * @return array<string, list<string>>
     */
    public function directoriesByDisk(EloquentMedia $media): array
    {
        $generator       = PathGeneratorFactory::create($media);
        $directories     = [
            $media->disk => [trim($generator->getPath($media), '/')],
        ];
        $conversionsDisk = $media->conversions_disk ?: $media->disk;

        if ($conversionsDisk !== $media->disk) {
            $directories[$conversionsDisk] = [
                trim($generator->getPathForConversions($media), '/'),
                trim($generator->getPathForResponsiveImages($media), '/'),
            ];
        }

        return $directories;
    }
}
