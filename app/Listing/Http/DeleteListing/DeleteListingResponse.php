<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListing;

use App\Listing\Application\UseCases\DeleteListing\DeleteListingOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeleteListingOutput
 */
class DeleteListingResponse extends JsonResource
{
    /**
     * @return array{deleted: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'deleted' => $this->deleted,
        ];
    }
}
