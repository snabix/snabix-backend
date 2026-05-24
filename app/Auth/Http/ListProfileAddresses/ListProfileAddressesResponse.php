<?php

declare(strict_types=1);

namespace App\Auth\Http\ListProfileAddresses;

use App\Auth\Application\UseCases\ListProfileAddresses\ListProfileAddressesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListProfileAddressesOutput
 */
class ListProfileAddressesResponse extends JsonResource
{
    /**
     * @return array{addresses: list<array<array-key, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'addresses' => $this->addresses,
        ];
    }
}
