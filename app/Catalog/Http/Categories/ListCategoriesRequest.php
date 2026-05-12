<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ListCategoriesRequest',
    properties: [
        new OA\Property(property: 'only_active', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'tree', type: 'boolean', example: true, nullable: true),
    ],
    type: 'object',
)]
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

    public function authorize(): bool
    {
        return true;
    }

    public function onlyActive(): bool
    {
        return $this->boolean('only_active', true);
    }

    public function tree(): bool
    {
        return $this->boolean('tree', true);
    }
}
