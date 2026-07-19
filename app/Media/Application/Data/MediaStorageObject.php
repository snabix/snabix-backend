<?php

declare(strict_types=1);

namespace App\Media\Application\Data;

final readonly class MediaStorageObject
{
    public function __construct(
        public string $disk,
        public string $path,
    ) {}

    /**
     * @return array{disk: string, path: string}
     */
    public function toArray(): array
    {
        return [
            'disk' => $this->disk,
            'path' => $this->path,
        ];
    }
}
