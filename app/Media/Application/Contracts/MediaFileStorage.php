<?php

declare(strict_types=1);

namespace App\Media\Application\Contracts;

use App\Media\Application\Data\StoredMediaFile;

interface MediaFileStorage
{
    public function inspect(string $disk, string $path): StoredMediaFile;

    public function copyVerified(StoredMediaFile $source, string $targetDisk, string $targetPath): StoredMediaFile;

    /**
     * @return list<string>
     */
    public function files(string $disk, string $directory): array;

    public function delete(string $disk, string $path): void;
}
