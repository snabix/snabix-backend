<?php

declare(strict_types=1);

namespace App\Catalog\Http\CreateCategoryAttributeDefinition;

use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryAttributeDefinitionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'categoryId'         => ['required', 'integer', 'min:1'],
            'name'               => ['required', 'string', 'max:255'],
            'slug'               => ['nullable', 'string', 'max:255'],
            'type'               => ['required', 'integer', 'min:1'],
            'unit'               => ['nullable', 'string', 'max:32'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'placeholder'        => ['nullable', 'string', 'max:255'],
            'helpText'           => ['nullable', 'string', 'max:2000'],
            'defaultValue'       => ['nullable', 'array'],
            'groupName'          => ['nullable', 'string', 'max:120'],
            'options'            => ['nullable', 'array'],
            'options.*'          => ['nullable'],
            'isRequired'         => ['nullable', 'boolean'],
            'isFilterable'       => ['nullable', 'boolean'],
            'showInCard'         => ['nullable', 'boolean'],
            'isActive'           => ['nullable', 'boolean'],
            'appliesToChildren'  => ['nullable', 'boolean'],
            'sortOrder'          => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
