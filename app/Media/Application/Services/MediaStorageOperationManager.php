<?php

declare(strict_types=1);

namespace App\Media\Application\Services;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Application\Data\MediaStorageObject;
use App\Media\Application\Data\PreparedMediaFile;
use App\Media\Application\Data\StoredMediaFile;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MediaStorageOperationManager
{
    public function __construct(
        private readonly MediaFileStorage $storage,
        private readonly MediaStorageKeyFactory $keys,
        private readonly MediaStoragePathResolver $paths,
        private readonly MediaStorageCleanupDispatcher $cleanup,
    ) {}

    public function inspect(string $disk, string $path): StoredMediaFile
    {
        return $this->storage->inspect($disk, $path);
    }

    public function inspectMedia(EloquentMedia $media): StoredMediaFile
    {
        return $this->inspect($media->disk, $this->paths->originalPath($media));
    }

    public function prepare(StoredMediaFile $source, EloquentMedia $candidate): PreparedMediaFile
    {
        $operationId   = (string) Str::uuid();
        $stagingDisk   = $this->keys->stagingDisk();
        $stagingPath   = $this->keys->stagingPath($operationId, $candidate->file_name);
        $permanentPath = $this->keys->permanentPath($candidate, $operationId, $candidate->file_name);
        $stagingObject = new MediaStorageObject($stagingDisk, $stagingPath);
        $targetObject  = new MediaStorageObject($candidate->disk, $permanentPath);

        try {
            $staged    = $this->storage->copyVerified($source, $stagingDisk, $stagingPath);
            $permanent = $this->storage->copyVerified($staged, $candidate->disk, $permanentPath);

            return new PreparedMediaFile($source, $staged, $permanent);
        } catch (Throwable $exception) {
            $this->cleanup->cleanupOrDefer([$stagingObject, $targetObject]);

            throw $exception;
        }
    }

    public function cleanupPrepared(PreparedMediaFile $prepared): void
    {
        $this->cleanup->cleanupOrDefer([
            $prepared->staged->object(),
            $prepared->permanent->object(),
        ]);
    }

    /**
     * @param list<MediaStorageObject> $objects
     */
    public function cleanup(array $objects): void
    {
        $this->cleanup->cleanupOrDefer($objects);
    }

    /**
     * @return list<MediaStorageObject>
     */
    public function mediaObjects(EloquentMedia $media): array
    {
        $originalPath = $this->paths->originalPath($media);
        $objects      = [
            $media->disk . "\0" . $originalPath => new MediaStorageObject($media->disk, $originalPath),
        ];

        foreach ($this->paths->directoriesByDisk($media) as $disk => $directories) {
            foreach ($directories as $directory) {
                foreach ($this->storage->files($disk, $directory) as $path) {
                    $objects[$disk . "\0" . $path] = new MediaStorageObject($disk, $path);
                }
            }
        }

        return array_values($objects);
    }

    /**
     * @return array{disk: mixed, file_name: mixed, collection_name: mixed, media_type: mixed, storage_key: mixed}
     */
    public function snapshot(EloquentMedia $media): array
    {
        return [
            'disk'            => $media->getRawOriginal('disk'),
            'file_name'       => $media->getRawOriginal('file_name'),
            'collection_name' => $media->getRawOriginal('collection_name'),
            'media_type'      => $media->getRawOriginal('media_type'),
            'storage_key'     => $media->getRawOriginal('storage_key'),
        ];
    }

    /**
     * @param array{disk: mixed, file_name: mixed, collection_name: mixed, media_type: mixed, storage_key: mixed} $snapshot
     */
    public function assertSnapshot(EloquentMedia $current, array $snapshot): void
    {
        foreach ($snapshot as $attribute => $expected) {
            if ($current->getRawOriginal($attribute) !== $expected) {
                throw new RuntimeException('Media storage metadata changed during the operation. Please retry.');
            }
        }
    }
}
