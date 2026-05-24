<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Auth\Application\UseCases\DeleteProfileAddress\DeleteProfileAddressOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeleteProfileAddressOutput
 */
class DeleteProfileAddressResponse extends JsonResource
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
