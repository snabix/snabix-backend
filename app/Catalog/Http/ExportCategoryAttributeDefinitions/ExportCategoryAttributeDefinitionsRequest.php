<?php

declare(strict_types=1);

namespace App\Catalog\Http\ExportCategoryAttributeDefinitions;

use Illuminate\Foundation\Http\FormRequest;

class ExportCategoryAttributeDefinitionsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'onlyActive' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'onlyActive' => $this->boolean('onlyActive', false),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
