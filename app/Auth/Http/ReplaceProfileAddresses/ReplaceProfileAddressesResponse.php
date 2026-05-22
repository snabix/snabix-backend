<?php

declare(strict_types=1);

namespace App\Auth\Http\ReplaceProfileAddresses;

use App\Auth\Application\UseCases\ReplaceProfileAddresses\ReplaceProfileAddressesOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReplaceProfileAddressesOutput
 */
class ReplaceProfileAddressesResponse extends JsonResource
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
