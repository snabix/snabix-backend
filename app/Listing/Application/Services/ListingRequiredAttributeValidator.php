<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

readonly class ListingRequiredAttributeValidator
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $categoryAttributeDefinitionRepository,
    ) {}

    /**
     * @param array<array-key, mixed> $attributeValues
     */
    public function validateSubmittedValues(int $categoryId, array $attributeValues): void
    {
        $requiredDefinitions = $this->requiredDefinitions($categoryId);

        if ($requiredDefinitions->isEmpty()) {
            return;
        }

        $submittedValues     = $this->normalizeSubmittedAttributeValues($attributeValues);

        foreach ($requiredDefinitions as $definition) {
            if (! array_key_exists($definition->id, $submittedValues) || ! $this->isFilledValue($submittedValues[$definition->id])) {
                $this->throwRequiredValidationException($definition);
            }
        }
    }

    public function validateStoredValues(EloquentListing $listing, int $categoryId): void
    {
        $requiredDefinitions = $this->requiredDefinitions($categoryId);

        if ($requiredDefinitions->isEmpty()) {
            return;
        }

        $listing->loadMissing('attributeValues');
        $storedValues        = $listing->attributeValues->keyBy('attribute_definition_id');

        foreach ($requiredDefinitions as $definition) {
            $storedValue = $storedValues->get($definition->id);

            if ($storedValue === null || ! $this->isFilledValue($storedValue->value)) {
                $this->throwRequiredValidationException($definition);
            }
        }
    }

    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    private function requiredDefinitions(int $categoryId): Collection
    {
        return $this->categoryAttributeDefinitionRepository
            ->forCategory($categoryId)
            ->filter(static fn(EloquentCategoryAttributeDefinition $definition): bool => (bool) $definition->is_required)
            ->values();
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

    private function isFilledValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return array_filter($value, static fn(mixed $item): bool => $item !== null && $item !== '') !== [];
        }

        return true;
    }

    private function throwRequiredValidationException(EloquentCategoryAttributeDefinition $definition): never
    {
        throw ValidationException::withMessages([
            'attributeValues.' . $definition->id => [sprintf('Поле "%s" обязательно для заполнения.', $definition->name)],
        ]);
    }
}
