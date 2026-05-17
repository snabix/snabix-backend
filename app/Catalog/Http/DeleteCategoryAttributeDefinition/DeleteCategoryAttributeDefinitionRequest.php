<?php

declare(strict_types=1);

namespace App\Catalog\Http\DeleteCategoryAttributeDefinition;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCategoryAttributeDefinitionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
