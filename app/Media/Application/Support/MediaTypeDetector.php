<?php

declare(strict_types=1);

namespace App\Media\Application\Support;

use App\Media\Domain\Enums\MediaType;

class MediaTypeDetector
{
    public function detect(?string $mimeType, ?string $extension = null): MediaType
    {
        $mimeType  = strtolower((string) $mimeType);
        $extension = strtolower((string) $extension);

        if (str_starts_with($mimeType, 'image/')) {
            return MediaType::IMAGE;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return MediaType::VIDEO;
        }

        if (
            str_contains($mimeType, 'pdf')
            || str_contains($mimeType, 'document')
            || str_contains($mimeType, 'spreadsheet')
            || str_contains($mimeType, 'presentation')
            || in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'], true)
        ) {
            return MediaType::DOCUMENT;
        }

        return MediaType::FILE;
    }
}
