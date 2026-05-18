<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Services;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Illuminate\Validation\ValidationException;

readonly class ListingAttributeValueSynchronizer
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $categoryAttributeDefinitionRepository,
    ) {}

    /**
     * @param array<array-key, mixed> $attributeValues
     */
    public function sync(
        EloquentListing $listing,
        int $categoryId,
        array $attributeValues,
        bool $validateRequiredAttributes = true,
    ): void {
        $definitions     = $this->categoryAttributeDefinitionRepository
            ->forCategory($categoryId)
            ->keyBy('id');
        $submittedValues = $this->normalizeSubmittedAttributeValues($attributeValues);
        $definitionIds   = $definitions->keys()->map(static fn(mixed $definitionId): int => (int) $definitionId)->all();

        if ($definitionIds === []) {
            $listing->attributeValues()->delete();

            return;
        }

        foreach ($definitions as $definition) {
            $definitionId    = $definition->id;
            $hasValue        = array_key_exists($definitionId, $submittedValues);

            if (! $hasValue) {
                if ($validateRequiredAttributes && $definition->is_required) {
                    throw ValidationException::withMessages([
                        'attributeValues.' . $definitionId => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
                    ]);
                }

                $this->deleteAttributeValue($listing, $definitionId);

                continue;
            }

            $normalizedValue = $this->normalizeDefinitionValue(
                definition: $definition,
                value: $submittedValues[$definitionId],
                validateRequired: $validateRequiredAttributes,
            );

            if ($normalizedValue === null) {
                $this->deleteAttributeValue($listing, $definitionId);

                continue;
            }

            EloquentListingAttributeValue::query()->updateOrCreate(
                [
                    'listing_id'              => $listing->id,
                    'attribute_definition_id' => $definitionId,
                ],
                [
                    'value'         => $normalizedValue,
                    'display_value' => $this->displayAttributeValue($normalizedValue),
                ],
            );
        }

        $listing
            ->attributeValues()
            ->whereNotIn('attribute_definition_id', $definitionIds)
            ->delete();
    }

    public function ensureRequiredValuesPresent(
        EloquentListing $listing,
        int $categoryId,
    ): void {
        $requiredDefinitions = $this->categoryAttributeDefinitionRepository
            ->forCategory($categoryId)
            ->filter(static fn(EloquentCategoryAttributeDefinition $definition): bool => (bool) $definition->is_required);

        if ($requiredDefinitions->isEmpty()) {
            return;
        }

        $listing->loadMissing('attributeValues');
        $storedValues        = $listing->attributeValues->keyBy('attribute_definition_id');

        foreach ($requiredDefinitions as $definition) {
            $storedValue = $storedValues->get($definition->id);

            if ($storedValue === null || ! $this->isFilledValue($storedValue->value)) {
                throw ValidationException::withMessages([
                    'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
                ]);
            }
        }
    }

    /**
     * @param  array<array-key, mixed> $attributeValues
     * @return array<int, mixed>
     */
    private function normalizeSubmittedAttributeValues(array $attributeValues): array
    {
        $normalizedValues = [];

        foreach ($attributeValues as $attributeDefinitionId => $value) {
            if (! is_numeric($attributeDefinitionId)) {
                continue;
            }

            $normalizedValues[(int) $attributeDefinitionId] = $value;
        }

        return $normalizedValues;
    }

    private function deleteAttributeValue(
        EloquentListing $listing,
        int $definitionId,
    ): void {
        $listing
            ->attributeValues()
            ->where('attribute_definition_id', $definitionId)
            ->delete();
    }

    private function isFilledValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return ! (is_array($value) && $value === []);
    }

    private function normalizeDefinitionValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
        bool $validateRequired = true,
    ): mixed {
        if ($value === null || $value === '') {
            if ($validateRequired && $definition->is_required) {
                throw ValidationException::withMessages([
                    'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
                ]);
            }

            return null;
        }

        if ($value === []) {
            if ($validateRequired && $definition->is_required) {
                throw ValidationException::withMessages([
                    'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
                ]);
            }

            return null;
        }

        return match ($definition->type) {
            CategoryAttributeType::TEXT        => $this->normalizeTextAttributeValue($definition, $value),
            CategoryAttributeType::NUMBER      => $this->normalizeNumberAttributeValue($definition, $value),
            CategoryAttributeType::BOOLEAN     => $this->normalizeBooleanAttributeValue($definition, $value),
            CategoryAttributeType::SELECT      => $this->normalizeSelectAttributeValue($definition, $value),
            CategoryAttributeType::MULTISELECT => $this->normalizeMultiselectAttributeValue($definition, $value),
            CategoryAttributeType::DATE        => $this->normalizeDateAttributeValue($definition, $value),
        };
    }

    private function normalizeTextAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): string {
        if (! is_scalar($value)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" должно быть текстом.', $definition->name)],
            ]);
        }

        $resolvedValue = trim((string) $value);

        if ($resolvedValue === '' && $definition->is_required) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
            ]);
        }

        return $resolvedValue;
    }

    private function normalizeNumberAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): float | int {
        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" должно быть числом.', $definition->name)],
            ]);
        }

        $resolvedValue = (float) $value;

        return floor($resolvedValue) === $resolvedValue
            ? (int) $resolvedValue
            : round($resolvedValue, 2);
    }

    private function normalizeBooleanAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (in_array($value, [0, 1], true)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $normalizedValue = mb_strtolower(trim($value));

            if (in_array($normalizedValue, ['1', 'true', 'yes', 'да'], true)) {
                return true;
            }

            if (in_array($normalizedValue, ['0', 'false', 'no', 'нет'], true)) {
                return false;
            }
        }

        throw ValidationException::withMessages([
            'attributeValues.' . $definition->id => [sprintf('Поле "%s" должно иметь значение Да или Нет.', $definition->name)],
        ]);
    }

    private function normalizeSelectAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): string {
        if (! is_scalar($value)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Для поля "%s" нужно выбрать одно значение.', $definition->name)],
            ]);
        }

        $resolvedValue = trim((string) $value);
        $options       = $this->normalizedDefinitionOptions($definition);

        if (! in_array($resolvedValue, $options, true)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Для поля "%s" выбрано недопустимое значение.', $definition->name)],
            ]);
        }

        return $resolvedValue;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeMultiselectAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): array {
        if (! is_array($value)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Для поля "%s" нужно выбрать несколько значений списком.', $definition->name)],
            ]);
        }

        $options          = $this->normalizedDefinitionOptions($definition);
        $normalizedValues = collect($value)
            ->map(static fn(mixed $item): ?string => is_scalar($item) ? trim((string) $item) : null)
            ->filter(static fn(?string $item): bool => $item !== null && $item !== '')
            ->unique()
            ->values()
            ->all();

        if ($normalizedValues === [] && $definition->is_required) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
            ]);
        }

        foreach ($normalizedValues as $normalizedValue) {
            if (! in_array($normalizedValue, $options, true)) {
                throw ValidationException::withMessages([
                    'attributeValues.' . $definition->id => [sprintf('Для поля "%s" передано недопустимое значение.', $definition->name)],
                ]);
            }
        }

        return $normalizedValues;
    }

    private function normalizeDateAttributeValue(
        EloquentCategoryAttributeDefinition $definition,
        mixed $value,
    ): string {
        if (! is_string($value)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" должно быть датой в формате YYYY-MM-DD.', $definition->name)],
            ]);
        }

        $resolvedValue = trim($value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $resolvedValue)) {
            throw ValidationException::withMessages([
                'attributeValues.' . $definition->id => [sprintf('Поле "%s" должно быть датой в формате YYYY-MM-DD.', $definition->name)],
            ]);
        }

        return $resolvedValue;
    }

    /**
     * @return array<int, string>
     */
    private function normalizedDefinitionOptions(EloquentCategoryAttributeDefinition $definition): array
    {
        $options = $definition->options;

        if (! is_array($options)) {
            return [];
        }

        return collect($options)
            ->map(static fn(mixed $option): ?string => is_scalar($option) ? trim((string) $option) : null)
            ->filter(static fn(?string $option): bool => $option !== null && $option !== '')
            ->values()
            ->all();
    }

    private function displayAttributeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $normalizedValues = array_map(
                static fn(mixed $item): string => is_scalar($item)
                    ? (string) $item
                    : (json_encode($item, JSON_UNESCAPED_UNICODE) ?: ''),
                $value,
            );

            return implode(', ', array_filter($normalizedValues, static fn(string $item): bool => $item !== ''));
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        return is_scalar($value) ? (string) $value : null;
    }
}
