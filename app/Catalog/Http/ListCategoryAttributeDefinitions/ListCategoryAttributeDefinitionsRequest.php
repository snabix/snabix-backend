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

    /**
     * @return array<string, bool>
     */
    public function inputData(): array
    {
        return [
            'onlyActive' => $this->boolean('only_active', false),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
