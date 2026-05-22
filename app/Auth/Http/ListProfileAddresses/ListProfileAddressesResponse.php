<?php

declare(strict_types=1);

namespace App\Auth\Http\ListProfileAddresses;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListProfileAddressesResponse extends JsonResource
{
    /**
     * @return array{addresses: list<array<array-key, mixed>>}
     */
    public function toArray(Request $request): array
    {
        return [
            'addresses' => $this->addresses(),
        ];
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    private function addresses(): array
    {
        if (! is_array($this->resource)) {
            return [];
        }

        return array_values(array_filter(
            $this->resource,
            fn(mixed $address): bool => is_array($address),
        ));
    }
}
