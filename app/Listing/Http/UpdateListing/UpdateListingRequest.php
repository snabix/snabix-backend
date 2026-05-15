<?php

declare(strict_types=1);

namespace App\Listing\Http\UpdateListing;

use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
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
            'status'           => ['required', 'integer', Rule::enum(ListingStatus::class)],
            'condition'        => ['nullable', 'integer', Rule::enum(ListingCondition::class)],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string', 'max:10000'],
            'price'            => ['nullable', 'integer', 'min:0'],
            'currency'         => ['nullable', 'string', 'size:3'],
            'isNegotiable'     => ['nullable', 'boolean'],
            'contactName'      => ['nullable', 'string', 'max:120'],
            'contactPhone'     => ['nullable', 'string', 'max:32'],
            'contactEmail'     => ['nullable', 'email', 'max:255'],
            'isFeatured'       => ['nullable', 'boolean'],
            'rejectionReason'  => ['nullable', 'string', 'max:5000'],
            'attributeValues'  => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
