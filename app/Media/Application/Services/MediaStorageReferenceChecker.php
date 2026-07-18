<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Data\MediaStorageObject;
use App\Media\Infrastructure\Models\EloquentMedia;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class MediaStorageReferenceChecker
{
    public function __construct(
        private readonly MediaStoragePathResolver $paths,
    ) {}

    public function isReferenced(MediaStorageObject $object): bool
    {
        $mediaItems = EloquentMedia::query()
            ->where('disk', $object->disk)
            ->orWhere('conversions_disk', $object->disk)
            ->cursor();

        foreach ($mediaItems as $media) {
            if ($media->disk === $object->disk && $this->paths->originalPath($media) === $object->path) {
                return true;
            }

            $conversionsDisk = $media->conversions_disk ?: $media->disk;

            if ($conversionsDisk !== $object->disk) {
                continue;
            }

            $generator       = PathGeneratorFactory::create($media);

            if (
                str_starts_with($object->path, $generator->getPathForConversions($media))
                || str_starts_with($object->path, $generator->getPathForResponsiveImages($media))
            ) {
                return true;
            }
        }

        return false;
    }
}
