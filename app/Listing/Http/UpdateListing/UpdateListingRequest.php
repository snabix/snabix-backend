<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categoryId'       => ['required', 'integer', 'min:1'],
            'type'             => ['required', 'integer', Rule::enum(ListingType::class)],
            'condition'        => ['nullable', 'integer', Rule::enum(ListingCondition::class)],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:10000'],
            'price'            => ['nullable', 'integer', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'isNegotiable'     => ['nullable', 'boolean'],
            'contactName'      => ['nullable', 'string', 'max:120'],
            'contactPhone'     => ['nullable', 'string', 'max:32'],
            'contactEmail'     => ['nullable', 'email', 'max:255'],
            'attributeValues'  => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    public function attributeValues(): array
    {
        $attributeValues = $this->input('attributeValues');

        return is_array($attributeValues) ? $attributeValues : [];
    }

    public function listingId(): string
    {
        $listingId = $this->route('listingId');

        return is_string($listingId) ? $listingId : '';
    }

    public function userId(): string
    {
        $user       = $this->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }

    public function authorize(): bool
    {
        return true;
    }
}
