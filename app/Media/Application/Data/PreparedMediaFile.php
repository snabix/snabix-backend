<?php

declare(strict_types=1);

namespace App\Media\Application\Data;

final readonly class PreparedMediaFile
{
    public function __construct(
        public StoredMediaFile $source,
        public StoredMediaFile $staged,
        public StoredMediaFile $permanent,
    ) {}
}
