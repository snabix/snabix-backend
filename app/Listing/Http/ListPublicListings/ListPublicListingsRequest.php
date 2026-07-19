<?php

declare(strict_types=1);

namespace App\Listing\Http\ListPublicListings;

use App\Listing\Domain\Enums\ListingType;
use App\Listing\Http\Support\ResolvesListingApiFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPublicListingsRequest extends FormRequest
{
    use ResolvesListingApiFields;

    public const DEFAULT_SORT = 'newest';

    public const SORT_VALUES  = [
        'newest',
        'oldest',
        'price_asc',
        'price_desc',
        'popular',
    ];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page'                => ['nullable', 'integer', 'min:1'],
            'perPage'             => ['nullable', 'integer', 'min:1', 'max:48'],
            'categoryId'          => ['nullable', 'uuid', 'exists:categories,id'],
            'listingKind'         => ['nullable', 'string', Rule::in(self::listingKindValues()), 'prohibits:type'],
            'minPriceAmountMinor' => ['nullable', 'integer', 'min:0', 'prohibits:minPrice'],
            'maxPriceAmountMinor' => ['nullable', 'integer', 'min:0', 'gte:minPriceAmountMinor', 'prohibits:maxPrice'],
            // Deprecated compatibility aliases. Remove after 2026-10-31.
            'type'                => ['nullable', 'integer', Rule::enum(ListingType::class)],
            'minPrice'            => ['nullable', 'integer', 'min:0'],
            'maxPrice'            => ['nullable', 'integer', 'min:0', 'gte:minPrice'],
            'regionId'            => ['nullable', 'integer', 'min:1', 'exists:regions,id'],
            'cityId'              => ['nullable', 'integer', 'min:1', 'exists:cities,id'],
            'regionQuery'         => ['nullable', 'string', 'max:120'],
            'cityQuery'           => ['nullable', 'string', 'max:120'],
            'isNegotiable'        => ['nullable', Rule::in(['true', 'false', '1', '0', true, false, 1, 0])],
            'sort'                => ['nullable', 'string', Rule::in(self::SORT_VALUES)],
        ];
    }

    /**
     * @return array<string, bool|int|string|null>
     */
    public function inputData(): array
    {
        return [
            'page'         => $this->integer('page', 1),
            'perPage'      => $this->integer('perPage', 15),
            'categoryId'   => $this->nullableStringInput('categoryId'),
            'type'         => $this->nullableListingTypeValue(),
            'minPrice'     => $this->nullableMoneyAmount('minPriceAmountMinor', 'minPrice'),
            'maxPrice'     => $this->nullableMoneyAmount('maxPriceAmountMinor', 'maxPrice'),
            'regionId'     => $this->nullableIntegerInput('regionId'),
            'cityId'       => $this->nullableIntegerInput('cityId'),
            'regionQuery'  => $this->nullableStringInput('regionQuery'),
            'cityQuery'    => $this->nullableStringInput('cityQuery'),
            'isNegotiable' => $this->nullableBooleanInput('isNegotiable'),
            'sort'         => $this->string('sort', self::DEFAULT_SORT)->toString(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    private function nullableStringInput(string $key): ?string
    {
        $value = $this->input($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function nullableBooleanInput(string $key): ?bool
    {
        if (! $this->exists($key)) {
            return null;
        }

        $value = filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return is_bool($value) ? $value : null;
    }
}
