<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListListingsRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

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
            'categoryId' => ['nullable', 'uuid', 'exists:categories,id'],
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
            'status'     => $this->nullableIntegerInput('status'),
            'type'       => $this->nullableIntegerInput('type'),
            'categoryId' => $this->nullableStringInput('categoryId'),
        ];
    }

    public function nullableIntegerInput(string $key): ?int
    {
        $value = $this->input($key);

        return is_numeric($value) ? (int) $value : null;
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
