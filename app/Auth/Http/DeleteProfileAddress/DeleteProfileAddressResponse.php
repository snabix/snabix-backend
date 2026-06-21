<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Auth\Application\UseCases\DeleteProfileAddress\DeleteProfileAddressOutput;
use App\Shared\Http\Resources\OutputResource;
use Illuminate\Http\Request;

/**
 * @mixin DeleteProfileAddressOutput
 */
class DeleteProfileAddressResponse extends OutputResource
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
