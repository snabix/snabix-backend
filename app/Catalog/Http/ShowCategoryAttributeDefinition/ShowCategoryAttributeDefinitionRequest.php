<?php

declare(strict_types=1);

namespace App\Catalog\Http\ShowCategoryAttributeDefinition;

use Illuminate\Foundation\Http\FormRequest;

class ShowCategoryAttributeDefinitionRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function attributeDefinitionId(): int
    {
        return (int) $this->route('attributeDefinitionId');
    }

    public function authorize(): bool
    {
        return true;
    }
}
