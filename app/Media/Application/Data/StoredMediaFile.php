<?php

declare(strict_types=1);

namespace App\Media\Application\Data;

final readonly class StoredMediaFile
{
    public function __construct(
        public string $disk,
        public string $path,
        public int $size,
        public string $checksum,
        public ?string $mimeType,
    ) {}

    public function object(): MediaStorageObject
    {
        return new MediaStorageObject($this->disk, $this->path);
    }
}
