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

    public function authorize(): bool
    {
        return true;
    }
}
