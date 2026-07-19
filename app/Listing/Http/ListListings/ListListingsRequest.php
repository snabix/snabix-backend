<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Http\Support\ResolvesListingApiFields;
use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListListingsRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;
    use ResolvesListingApiFields;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page'          => ['nullable', 'integer', 'min:1'],
            'perPage'       => ['nullable', 'integer', 'min:1', 'max:48'],
            'listingStatus' => ['nullable', 'string', Rule::in(self::listingStatusValues()), 'prohibits:status'],
            'listingKind'   => ['nullable', 'string', Rule::in(self::listingKindValues()), 'prohibits:type'],
            // Deprecated compatibility aliases. Remove after 2026-10-31.
            'status'        => ['nullable', 'integer', Rule::enum(ListingStatus::class)],
            'type'          => ['nullable', 'integer', Rule::enum(ListingType::class)],
            'categoryId'    => ['nullable', 'uuid', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'userId'     => $this->userId(),
            'page'       => $this->integer('page', 1),
            'perPage'    => $this->integer('perPage', 12),
            'status'     => $this->nullableListingStatusValue(),
            'type'       => $this->nullableListingTypeValue(),
            'categoryId' => $this->nullableStringInput('categoryId'),
        ];
    }

    public function nullableStringInput(string $key): ?string
    {
        $value = $this->input($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function authorize(): bool
    {
        return true;
    }
}
