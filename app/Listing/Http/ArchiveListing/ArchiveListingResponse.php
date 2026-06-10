<?php

declare(strict_types=1);

namespace App\Listing\Http\ArchiveListing;

use App\Listing\Application\UseCases\ArchiveListing\ArchiveListingOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ArchiveListingOutput
 */
class ArchiveListingResponse extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->item;
    }
}
