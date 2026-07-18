<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Storage;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Application\Data\StoredMediaFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LaravelMediaFileStorage implements MediaFileStorage
{
    public function inspect(string $disk, string $path): StoredMediaFile
    {
        $filesystem   = Storage::disk($disk);

        if (! $filesystem->exists($path)) {
            throw new RuntimeException(sprintf('Media object [%s:%s] was not found.', $disk, $path));
        }

        $stream       = $filesystem->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException(sprintf('Unable to read media object [%s:%s].', $disk, $path));
        }

        $hash         = hash_init('sha256');
        $streamSize   = 0;

        try {
            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 1024);

                if ($chunk === false) {
                    throw new RuntimeException(sprintf('Unable to checksum media object [%s:%s].', $disk, $path));
                }

                $streamSize += strlen($chunk);
                hash_update($hash, $chunk);
            }
        } finally {
            fclose($stream);
        }

        $reportedSize = $filesystem->size($path);

        if ($reportedSize !== $streamSize) {
            throw new RuntimeException(sprintf('Media object size mismatch for [%s:%s].', $disk, $path));
        }

        $mimeType     = $filesystem->mimeType($path);

        return new StoredMediaFile(
            disk: $disk,
            path: $path,
            size: $streamSize,
            checksum: hash_final($hash),
            mimeType: is_string($mimeType) ? $mimeType : null,
        );
    }

    public function copyVerified(StoredMediaFile $source, string $targetDisk, string $targetPath): StoredMediaFile
    {
        $sourceFilesystem = Storage::disk($source->disk);
        $stream           = $sourceFilesystem->readStream($source->path);

        if (! is_resource($stream)) {
            throw new RuntimeException(sprintf(
                'Unable to read media object [%s:%s] for copy.',
                $source->disk,
                $source->path,
            ));
        }

        try {
            $stored = Storage::disk($targetDisk)->put($targetPath, $stream);
        } finally {
            fclose($stream);
        }

        if ($stored !== true) {
            throw new RuntimeException(sprintf('Unable to write media object [%s:%s].', $targetDisk, $targetPath));
        }

        $target           = $this->inspect($targetDisk, $targetPath);

        if ($target->size !== $source->size || ! hash_equals($source->checksum, $target->checksum)) {
            throw new RuntimeException(sprintf(
                'Media object verification failed for [%s:%s].',
                $targetDisk,
                $targetPath,
            ));
        }

        return $target;
    }

    public function files(string $disk, string $directory): array
    {
        $files = Storage::disk($disk)->allFiles(trim($directory, '/'));

        return array_values(array_filter($files, is_string(...)));
    }

    public function delete(string $disk, string $path): void
    {
        $filesystem = Storage::disk($disk);

        if (! $filesystem->exists($path)) {
            return;
        }

        if (! $filesystem->delete($path)) {
            throw new RuntimeException(sprintf('Unable to delete media object [%s:%s].', $disk, $path));
        }
    }
}
