<?php

declare(strict_types=1);

namespace App\Listing\Http\UploadListingMedia;

use App\Listing\Application\UseCases\UploadListingMedia\UploadListingMediaOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin UploadListingMediaOutput
 */
class UploadListingMediaResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
