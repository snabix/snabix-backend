<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Http\Support\ResolvesListingApiFields;
use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;
    use ResolvesListingApiFields;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categoryId'       => ['required', 'uuid', 'exists:categories,id'],
            'listingKind'      => ['required_without:type', 'string', Rule::in(self::listingKindValues()), 'prohibits:type'],
            'itemCondition'    => ['nullable', 'string', Rule::in(self::itemConditionValues()), 'prohibits:condition'],
            'priceAmountMinor' => ['nullable', 'integer', 'min:0', 'prohibits:price'],
            'priceCurrency'    => ['nullable', 'string', 'size:3', 'prohibits:currency'],
            // Deprecated compatibility aliases. Remove after 2026-10-31.
            'type'             => ['required_without:listingKind', 'integer', Rule::enum(ListingType::class), 'prohibits:listingKind'],
            'condition'        => ['nullable', 'integer', Rule::enum(ListingCondition::class)],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:10000'],
            'price'            => ['nullable', 'integer', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'isNegotiable'     => ['nullable', 'boolean'],
            'contactName'      => ['nullable', 'string', 'max:120'],
            'contactPhone'     => ['nullable', 'string', 'max:32'],
            'contactEmail'     => ['nullable', 'email', 'max:255'],
            'addressMode'      => ['nullable', 'string', Rule::in(['profile', 'custom', 'none'])],
            'profileAddressId' => ['nullable', 'uuid'],
            'regionId'         => ['nullable', 'integer', 'min:1'],
            'cityId'           => ['nullable', 'integer', 'min:1'],
            'addressLine'      => ['nullable', 'string', 'max:255'],
            'attributeValues'  => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'userId'          => $this->userId(),
            'listingId'       => $this->listingId(),
            'categoryId'      => $this->string('categoryId')->toString(),
            'type'            => $this->listingTypeValue(),
            'condition'       => $this->nullableListingConditionValue(),
            'title'           => $this->string('title')->toString(),
            'description'     => $this->string('description')->toString(),
            'price'           => $this->nullableMoneyAmount('priceAmountMinor', 'price'),
            'currency'        => $this->nullableMoneyCurrency('priceCurrency', 'currency'),
            'isNegotiable'    => $this->boolean('isNegotiable', false),
            'contactName'     => $this->nullableStringInput('contactName'),
            'contactPhone'    => $this->nullableStringInput('contactPhone'),
            'contactEmail'    => $this->nullableStringInput('contactEmail'),
            'addressMode'     => $this->nullableStringInput('addressMode') ?? 'none',
            'profileAddressId'=> $this->nullableStringInput('profileAddressId'),
            'regionId'        => $this->nullableIntegerInput('regionId'),
            'cityId'          => $this->nullableIntegerInput('cityId'),
            'addressLine'     => $this->nullableStringInput('addressLine'),
            'attributeValues' => $this->attributeValues(),
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

    public function authorize(): bool
    {
        return true;
    }

    private function nullableStringInput(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->toString() : null;
    }
}
