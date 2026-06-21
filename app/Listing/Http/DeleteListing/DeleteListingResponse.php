<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListing;

use App\Listing\Application\UseCases\DeleteListing\DeleteListingOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin DeleteListingOutput
 */
class DeleteListingResponse extends OutputResource
{
    /**
     * @return array{deleted: bool}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
