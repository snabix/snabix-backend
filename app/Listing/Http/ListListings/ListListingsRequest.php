<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListListingsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page'       => ['nullable', 'integer', 'min:1'],
            'perPage'    => ['nullable', 'integer', 'min:1', 'max:48'],
            'status'     => ['nullable', 'integer', Rule::enum(ListingStatus::class)],
            'type'       => ['nullable', 'integer', Rule::enum(ListingType::class)],
            'categoryId' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function userId(): string
    {
        $user       = $this->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }

    public function nullableIntegerInput(string $key): ?int
    {
        $value = $this->input($key);

        return is_numeric($value) ? (int) $value : null;
    }

    public function authorize(): bool
    {
        return true;
    }
}
