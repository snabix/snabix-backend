<?php

declare(strict_types=1);

namespace App\Listing\Application\UseCases\UploadListingMedia;

use App\Shared\Domain\DTO\Input;
use Illuminate\Http\UploadedFile;

class UploadListingMediaInput extends Input
{
    /**
     * @param list<UploadedFile> $images
     */
    public function __construct(
        public readonly string $userId,
        public readonly string $listingId,
        public readonly array $images,
    ) {}
}
