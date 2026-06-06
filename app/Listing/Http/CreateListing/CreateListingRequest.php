<?php

declare(strict_types=1);

namespace App\Listing\Http\CreateListing;

use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingType;
use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateListingRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categoryId'       => ['required', 'uuid', 'exists:categories,id'],
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
            'saveAsDraft'      => ['nullable', 'boolean'],
            'attributeValues'  => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'userId'         => $this->userId(),
            'categoryId'     => $this->string('categoryId')->toString(),
            'type'           => $this->integer('type'),
            'condition'      => $this->nullableIntegerInput('condition'),
            'title'          => $this->string('title')->toString(),
            'description'    => $this->string('description')->toString(),
            'price'          => $this->nullableIntegerInput('price'),
            'currency'       => $this->nullableUppercaseString('currency'),
            'isNegotiable'   => $this->boolean('isNegotiable', false),
            'contactName'    => $this->nullableStringInput('contactName'),
            'contactPhone'   => $this->nullableStringInput('contactPhone'),
            'contactEmail'   => $this->nullableStringInput('contactEmail'),
            'saveAsDraft'    => $this->saveAsDraft(),
            'attributeValues'=> $this->attributeValues(),
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

    public function saveAsDraft(): bool
    {
        return $this->boolean('saveAsDraft', false);
    }

    public function authorize(): bool
    {
        return true;
    }

    private function nullableIntegerInput(string $key): ?int
    {
        $value = $this->input($key);

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    private function nullableStringInput(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->toString() : null;
    }

    private function nullableUppercaseString(string $key): ?string
    {
        $value = $this->input($key);

        return is_string($value) && $value !== '' ? mb_strtoupper($value) : null;
    }
}
