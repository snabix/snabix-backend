<?php

declare(strict_types=1);

namespace App\Listing\Http\ListPublicListings;

use Illuminate\Foundation\Http\FormRequest;

class ListPublicListingsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'page'    => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:48'],
        ];
    }

    /**
     * @return array<string, int>
     */
    public function inputData(): array
    {
        return [
            'page'    => $this->integer('page', 1),
            'perPage' => $this->integer('perPage', 24),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
