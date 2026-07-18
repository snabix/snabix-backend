<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Application\Data\MediaStorageObject;

class MediaStorageCleaner
{
    public function __construct(
        private readonly MediaFileStorage $storage,
        private readonly MediaStorageReferenceChecker $references,
    ) {}

    public function deleteIfUnreferenced(MediaStorageObject $object): void
    {
        if ($this->references->isReferenced($object)) {
            return;
        }

        $this->storage->delete($object->disk, $object->path);
    }
}
