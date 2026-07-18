<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Data\MediaStorageObject;
use App\Media\Application\Jobs\CleanupMediaStorageObjectsJob;
use Throwable;

class MediaStorageCleanupDispatcher
{
    public function __construct(
        private readonly MediaStorageCleaner $cleaner,
    ) {}

    /**
     * @param list<MediaStorageObject> $objects
     */
    public function cleanupOrDefer(array $objects): void
    {
        foreach ($this->unique($objects) as $object) {
            try {
                $this->cleaner->deleteIfUnreferenced($object);
            } catch (Throwable $cleanupException) {
                report($cleanupException);

                try {
                    CleanupMediaStorageObjectsJob::dispatch([$object->toArray()]);
                } catch (Throwable $dispatchException) {
                    report($dispatchException);
                }
            }
        }
    }

    /**
     * @param  list<MediaStorageObject> $objects
     * @return list<MediaStorageObject>
     */
    private function unique(array $objects): array
    {
        $unique = [];

        foreach ($objects as $object) {
            $unique[$object->disk . "\0" . $object->path] = $object;
        }

        return array_values($unique);
    }
}
