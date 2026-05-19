<?php

declare(strict_types=1);

namespace App\Catalog\Http\ListCategories;

use Illuminate\Foundation\Http\FormRequest;

class ListCategoriesRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'only_active' => ['nullable', 'boolean'],
            'tree'        => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function inputData(): array
    {
        return [
            'onlyActive' => $this->boolean('only_active', true),
            'tree'       => $this->boolean('tree', true),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
