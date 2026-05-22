<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeleteProfileAddressResponse extends JsonResource
{
    /**
     * @return array{deleted: bool}
     */
    public function toArray(Request $request): array
    {
        return [
            'deleted' => true,
        ];
    }
}
