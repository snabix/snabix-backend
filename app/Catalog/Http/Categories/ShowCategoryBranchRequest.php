<?php

declare(strict_types=1);

namespace App\Catalog\Http\Categories;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ShowCategoryBranchRequest',
    properties: [
        new OA\Property(property: 'only_active', type: 'boolean', example: true, nullable: true),
    ],
    type: 'object',
)]
class ShowCategoryBranchRequest extends FormRequest
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

    public function categoryId(): int
    {
        return (int) $this->route('categoryId');
    }

    public function onlyActive(): bool
    {
        return $this->boolean('only_active', true);
    }
}
