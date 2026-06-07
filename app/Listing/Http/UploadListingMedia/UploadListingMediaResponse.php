<?php

declare(strict_types=1);

namespace App\Listing\Http\UploadListingMedia;

use App\Listing\Application\UseCases\UploadListingMedia\UploadListingMediaOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UploadListingMediaOutput
 */
class UploadListingMediaResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
