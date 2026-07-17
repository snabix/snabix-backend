<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Infrastructure\Models\EloquentMedia;
use RuntimeException;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class MediaStorageKeyFactory
{
    public function stagingDisk(): string
    {
        $disk = config('media-library.staging_disk_name', 'local');

        if (! is_string($disk) || $disk === '') {
            throw new RuntimeException('Media staging disk is not configured.');
        }

        return $disk;
    }

    public function stagingPath(string $operationId, string $fileName): string
    {
        $prefix = config('media-library.staging_prefix', 'media-staging');
        $prefix = is_string($prefix) && $prefix !== '' ? trim($prefix, '/') : 'media-staging';

        return $prefix . '/' . $operationId . '/' . $fileName;
    }

    public function permanentPath(EloquentMedia $media, string $operationId, string $fileName): string
    {
        $candidate              = clone $media;
        $candidate->storage_key = null;
        $basePath               = trim(PathGeneratorFactory::create($candidate)->getPath($candidate), '/');

        return $basePath . '/versions/' . $operationId . '/' . $fileName;
    }
}
