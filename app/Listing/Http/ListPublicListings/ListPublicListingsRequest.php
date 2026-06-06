<?php

declare(strict_types=1);

namespace App\Listing\Http\ListPublicListings;

use App\Listing\Domain\Enums\ListingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPublicListingsRequest extends FormRequest
{
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
            'page'      => ['nullable', 'integer', 'min:1'],
            'perPage'   => ['nullable', 'integer', 'min:1', 'max:48'],
            'categoryId'=> ['nullable', 'uuid', 'exists:categories,id'],
            'type'      => ['nullable', 'integer', Rule::enum(ListingType::class)],
            'minPrice'  => ['nullable', 'integer', 'min:0'],
            'maxPrice'  => ['nullable', 'integer', 'min:0', 'gte:minPrice'],
            'sort'      => ['nullable', 'string', Rule::in(self::SORT_VALUES)],
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    public function inputData(): array
    {
        return [
            'page'      => $this->integer('page', 1),
            'perPage'   => $this->integer('perPage', 15),
            'categoryId'=> $this->nullableStringInput('categoryId'),
            'type'      => $this->nullableIntegerInput('type'),
            'minPrice'  => $this->nullableIntegerInput('minPrice'),
            'maxPrice'  => $this->nullableIntegerInput('maxPrice'),
            'sort'      => $this->string('sort', self::DEFAULT_SORT)->toString(),
        ];
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
        $value = $this->input($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
