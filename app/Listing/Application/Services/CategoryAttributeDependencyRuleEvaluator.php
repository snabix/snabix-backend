<?php

declare(strict_types=1);

namespace App\Listing\Application\Services;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Support\Collection;

readonly class CategoryAttributeDependencyRuleEvaluator
{
    /**
     * @param  Collection<int, EloquentCategoryAttributeDefinition> $definitions
     * @param  array<int, mixed>                                    $values
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function visibleDefinitions(Collection $definitions, array $values): Collection
    {
        return $definitions
            ->filter(fn(EloquentCategoryAttributeDefinition $definition): bool => $this->isVisible($definition, $definitions, $values))
            ->values();
    }

    /**
     * @param Collection<int, EloquentCategoryAttributeDefinition> $definitions
     * @param array<int, mixed>                                    $values
     * @param array<int, true>                                     $visitedDefinitionIds
     */
    private function isVisible(
        EloquentCategoryAttributeDefinition $definition,
        Collection $definitions,
        array $values,
        array $visitedDefinitionIds = [],
    ): bool {
        $rules                                 = $definition->dependency_rules;

        if (! is_array($rules) || $rules === []) {
            return true;
        }

        if (isset($visitedDefinitionIds[$definition->id])) {
            return false;
        }

        $visitedDefinitionIds[$definition->id] = true;

        foreach ($rules as $rule) {
            $normalizedRule       = $this->normalizeRule($rule);

            if ($normalizedRule === null) {
                return false;
            }

            $dependencyDefinition = $this->findDependencyDefinition($normalizedRule, $definitions);

            if ($dependencyDefinition === null) {
                return false;
            }

            if (! $this->isVisible($dependencyDefinition, $definitions, $values, $visitedDefinitionIds)) {
                return false;
            }

            $currentValue         = $values[$dependencyDefinition->id] ?? null;

            if (! $this->matchesRule(
                currentValue: $currentValue,
                operator: is_string($normalizedRule['operator'] ?? null) ? $normalizedRule['operator'] : 'equals',
                expectedValue: $normalizedRule['value'] ?? null,
            )) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeRule(mixed $rule): ?array
    {
        if (! is_array($rule)) {
            return null;
        }

        $normalizedRule = [];

        foreach ($rule as $key => $value) {
            if (! is_string($key)) {
                return null;
            }

            $normalizedRule[$key] = $value;
        }

        return $normalizedRule;
    }

    /**
     * @param array<string, mixed>                                 $rule
     * @param Collection<int, EloquentCategoryAttributeDefinition> $definitions
     */
    private function findDependencyDefinition(
        array $rule,
        Collection $definitions,
    ): ?EloquentCategoryAttributeDefinition {
        $attributeDefinitionId = $rule['attributeDefinitionId'] ?? null;

        if (is_numeric($attributeDefinitionId)) {
            return $definitions->first(
                fn(EloquentCategoryAttributeDefinition $definition): bool => $definition->id === (int) $attributeDefinitionId,
            );
        }

        $attributeSlug         = $rule['attributeSlug'] ?? null;

        if (is_string($attributeSlug) && trim($attributeSlug) !== '') {
            return $definitions->first(
                fn(EloquentCategoryAttributeDefinition $definition): bool => $definition->slug === trim($attributeSlug),
            );
        }

        return null;
    }

    private function matchesRule(
        mixed $currentValue,
        string $operator,
        mixed $expectedValue,
    ): bool {
        return match ($operator) {
            'filled'     => $this->isFilledValue($currentValue),
            'empty'      => ! $this->isFilledValue($currentValue),
            'equals'     => $this->compareValue($currentValue, $expectedValue),
            'not_equals' => ! $this->compareValue($currentValue, $expectedValue),
            'in'         => $this->isValueInExpectedList($currentValue, $expectedValue),
            'not_in'     => ! $this->isValueInExpectedList($currentValue, $expectedValue),
            default      => false,
        };
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
            return array_filter($value, fn(mixed $item): bool => $this->isFilledValue($item)) !== [];
        }

        return true;
    }

    private function compareValue(mixed $currentValue, mixed $expectedValue): bool
    {
        if (is_array($currentValue)) {
            return collect($currentValue)->contains(fn(mixed $item): bool => $this->compareScalarValue($item, $expectedValue));
        }

        return $this->compareScalarValue($currentValue, $expectedValue);
    }

    private function isValueInExpectedList(mixed $currentValue, mixed $expectedValue): bool
    {
        if (! is_array($expectedValue)) {
            return false;
        }

        if (is_array($currentValue)) {
            return collect($currentValue)
                ->contains(fn(mixed $item): bool => collect($expectedValue)->contains(fn(mixed $expected): bool => $this->compareScalarValue($item, $expected)));
        }

        return collect($expectedValue)->contains(fn(mixed $expected): bool => $this->compareScalarValue($currentValue, $expected));
    }

    private function compareScalarValue(mixed $currentValue, mixed $expectedValue): bool
    {
        if (is_bool($currentValue) || is_bool($expectedValue)) {
            return $this->normalizeBoolean($currentValue) === $this->normalizeBoolean($expectedValue);
        }

        if (is_numeric($currentValue) && is_numeric($expectedValue)) {
            return (string) $currentValue === (string) $expectedValue;
        }

        if (! is_scalar($currentValue) || ! is_scalar($expectedValue)) {
            return false;
        }

        return trim((string) $currentValue) === trim((string) $expectedValue);
    }

    private function normalizeBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (in_array($value, [1, '1', 'true', 'yes', 'да'], true)) {
            return true;
        }

        if (in_array($value, [0, '0', 'false', 'no', 'нет'], true)) {
            return false;
        }

        return null;
    }
}
