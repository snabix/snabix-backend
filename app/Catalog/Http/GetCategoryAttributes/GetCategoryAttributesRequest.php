<?php

declare(strict_types=1);

namespace App\Catalog\Http\GetCategoryAttributes;

use Illuminate\Foundation\Http\FormRequest;

class GetCategoryAttributesRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'only_active' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
