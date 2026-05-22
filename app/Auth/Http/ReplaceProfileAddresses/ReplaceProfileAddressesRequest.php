<?php

declare(strict_types=1);

namespace App\Auth\Http\ReplaceProfileAddresses;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceProfileAddressesRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'addresses'               => ['present', 'array', 'max:20'],
            'addresses.*.id'          => ['nullable', 'uuid'],
            'addresses.*.regionId'    => ['required', 'integer', 'min:1'],
            'addresses.*.cityId'      => ['nullable', 'integer', 'min:1'],
            'addresses.*.label'       => ['nullable', 'string', 'max:120'],
            'addresses.*.addressLine' => ['nullable', 'string', 'max:255'],
            'addresses.*.isPrimary'   => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return list<array<array-key, mixed>>
     */
    public function addresses(): array
    {
        $addresses = $this->input('addresses');

        if (!is_array($addresses)) {
            return [];
        }

        return array_values(
            array_filter(
                $addresses,
                fn(mixed $address): bool => is_array($address),
            ),
        );
    }

    public function authorize(): bool
    {
        return true;
    }
}
