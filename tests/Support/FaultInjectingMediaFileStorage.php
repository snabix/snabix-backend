<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Media\Application\Contracts\MediaFileStorage;
use App\Media\Application\Data\StoredMediaFile;
use RuntimeException;

class FaultInjectingMediaFileStorage implements MediaFileStorage
{
    private int $copyCalls = 0;

    /**
     * @param list<string> $deleteFailures
     */
    public function __construct(
        private readonly MediaFileStorage $storage,
        private readonly ?int $failAfterCopyCall = null,
        private readonly array $deleteFailures = [],
    ) {}

    public function inspect(string $disk, string $path): StoredMediaFile
    {
        return $this->storage->inspect($disk, $path);
    }

    public function copyVerified(StoredMediaFile $source, string $targetDisk, string $targetPath): StoredMediaFile
    {
        $copied = $this->storage->copyVerified($source, $targetDisk, $targetPath);
        $this->copyCalls++;

        if ($this->copyCalls === $this->failAfterCopyCall) {
            throw new RuntimeException('Injected media copy failure.');
        }

        return $copied;
    }

    public function files(string $disk, string $directory): array
    {
        return $this->storage->files($disk, $directory);
    }

    public function delete(string $disk, string $path): void
    {
        if (in_array($disk . ':' . $path, $this->deleteFailures, true)) {
            throw new RuntimeException('Injected media delete failure.');
        }

        $this->storage->delete($disk, $path);
    }
}
