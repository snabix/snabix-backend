<?php

declare(strict_types=1);

namespace App\Auth\Http\DeleteProfileAddress;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class DeleteProfileAddressRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array{userId: string, addressId: string}
     */
    public function inputData(): array
    {
        $addressId = $this->route('addressId');

        return [
            'userId'    => $this->userId(),
            'addressId' => is_string($addressId) ? $addressId : '',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
