<?php

declare(strict_types=1);

namespace App\Auth\Http\ReplaceProfileAddresses;

use App\Auth\Application\UseCases\ReplaceProfileAddresses\ReplaceProfileAddressesOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin ReplaceProfileAddressesOutput
 */
class ReplaceProfileAddressesResponse extends OutputResource
{
    /**
     * @return array{addresses: list<array<array-key, mixed>>}
     * @phpstan-return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
