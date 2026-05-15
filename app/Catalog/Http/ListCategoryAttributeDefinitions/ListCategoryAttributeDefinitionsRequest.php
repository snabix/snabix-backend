<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategoryAttributeDefinitions;

use Illuminate\Foundation\Http\FormRequest;

class ListCategoryAttributeDefinitionsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
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
