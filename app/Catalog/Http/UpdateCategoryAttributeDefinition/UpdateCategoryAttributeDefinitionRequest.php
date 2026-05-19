<?php

declare(strict_types=1);

namespace App\Catalog\Http\UpdateCategoryAttributeDefinition;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryAttributeDefinitionRequest extends FormRequest
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
            'dependencyRules'    => ['nullable', 'array'],
            'dependencyRules.*'  => ['array'],
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

    /**
     * @return array<string, mixed>
     */
    public function inputData(): array
    {
        return [
            'attributeDefinitionId' => $this->attributeDefinitionId(),
            'categoryId'            => $this->integer('categoryId'),
            'name'                  => $this->string('name')->toString(),
            'slug'                  => $this->nullableStringInput('slug'),
            'type'                  => $this->integer('type'),
            'unit'                  => $this->nullableStringInput('unit'),
            'description'           => $this->nullableStringInput('description'),
            'placeholder'           => $this->nullableStringInput('placeholder'),
            'helpText'              => $this->nullableStringInput('helpText'),
            'defaultValue'          => $this->arrayInputOrNull('defaultValue'),
            'dependencyRules'       => $this->arrayInputOrNull('dependencyRules'),
            'groupName'             => $this->nullableStringInput('groupName'),
            'options'               => $this->arrayInputOrNull('options'),
            'isRequired'            => $this->boolean('isRequired', false),
            'isFilterable'          => $this->boolean('isFilterable', false),
            'showInCard'            => $this->boolean('showInCard', false),
            'isActive'              => $this->boolean('isActive', true),
            'appliesToChildren'     => $this->boolean('appliesToChildren', true),
            'sortOrder'             => $this->integer('sortOrder'),
        ];
    }

    public function attributeDefinitionId(): int
    {
        return (int) $this->route('attributeDefinitionId');
    }

    public function authorize(): bool
    {
        return true;
    }

    private function nullableStringInput(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->toString() : null;
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function arrayInputOrNull(string $key): ?array
    {
        $value = $this->input($key);

        return is_array($value) ? array_values($value) : null;
    }
}
