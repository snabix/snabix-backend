<?php

declare(strict_types=1);

namespace App\Listing\Http\ArchiveListing;

use App\Listing\Application\UseCases\ArchiveListing\ArchiveListingOutput;
use App\Shared\Http\Resources\ItemOutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ArchiveListingOutput
 */
class ArchiveListingResponse extends ItemOutputResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
