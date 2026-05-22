<?php

declare(strict_types=1);

namespace App\Catalog\Http\ImportCategoryAttributeDefinitions;

use Illuminate\Foundation\Http\FormRequest;

class ImportCategoryAttributeDefinitionsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'items'                       => ['required', 'array', 'min:1'],
            'items.*'                     => ['required', 'array'],
            'items.*.categoryId'          => ['required_without:items.*.category_id', 'integer', 'min:1'],
            'items.*.category_id'         => ['required_without:items.*.categoryId', 'integer', 'min:1'],
            'items.*.name'                => ['required', 'string', 'max:255'],
            'items.*.slug'                => ['nullable', 'string', 'max:255'],
            'items.*.type'                => ['required', 'integer', 'min:1'],
            'items.*.unit'                => ['nullable', 'string', 'max:32'],
            'items.*.description'         => ['nullable', 'string', 'max:2000'],
            'items.*.placeholder'         => ['nullable', 'string', 'max:255'],
            'items.*.helpText'            => ['nullable', 'string', 'max:2000'],
            'items.*.help_text'           => ['nullable', 'string', 'max:2000'],
            'items.*.defaultValue'        => ['nullable', 'array'],
            'items.*.default_value'       => ['nullable', 'array'],
            'items.*.dependencyRules'     => ['nullable', 'array'],
            'items.*.dependency_rules'    => ['nullable', 'array'],
            'items.*.groupName'           => ['nullable', 'string', 'max:120'],
            'items.*.group_name'          => ['nullable', 'string', 'max:120'],
            'items.*.options'             => ['nullable', 'array'],
            'items.*.isRequired'          => ['nullable', 'boolean'],
            'items.*.is_required'         => ['nullable', 'boolean'],
            'items.*.isFilterable'        => ['nullable', 'boolean'],
            'items.*.is_filterable'       => ['nullable', 'boolean'],
            'items.*.showInCard'          => ['nullable', 'boolean'],
            'items.*.show_in_card'        => ['nullable', 'boolean'],
            'items.*.isActive'            => ['nullable', 'boolean'],
            'items.*.is_active'           => ['nullable', 'boolean'],
            'items.*.appliesToChildren'   => ['nullable', 'boolean'],
            'items.*.applies_to_children' => ['nullable', 'boolean'],
            'items.*.sortOrder'           => ['nullable', 'integer', 'min:0'],
            'items.*.sort_order'          => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'items' => $this->items(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        $items           = $this->input('items');

        if (! is_array($items)) {
            return [];
        }

        $normalizedItems = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalizedItem    = [];

            foreach ($item as $key => $value) {
                if (! is_string($key)) {
                    continue;
                }

                $normalizedItem[$key] = $value;
            }

            $normalizedItems[] = $normalizedItem;
        }

        return $normalizedItems;
    }

    public function authorize(): bool
    {
        return true;
    }
}
