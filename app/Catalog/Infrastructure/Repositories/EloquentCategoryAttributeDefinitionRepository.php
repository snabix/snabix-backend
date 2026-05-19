<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Repositories;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EloquentCategoryAttributeDefinitionRepository implements CategoryAttributeDefinitionRepositoryInterface
{
    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function list(bool $onlyActive = false): Collection
    {
        $query = EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, EloquentCategoryAttributeDefinition>
     */
    public function forCategory(int $categoryId, bool $onlyActive = true): Collection
    {
        $category         = EloquentCategory::query()->find($categoryId);

        if ($category === null) {
            return collect();
        }

        $directCategoryId = $category->id;
        $ancestorIds      = [];
        $currentParentId  = $category->parent_id;

        while ($currentParentId !== null) {
            $ancestorIds[]   = $currentParentId;
            $parentCategory  = EloquentCategory::query()->find($currentParentId);
            $currentParentId = $parentCategory?->parent_id;
        }

        $query            = EloquentCategoryAttributeDefinition::query()
            ->where(function ($builder) use ($directCategoryId, $ancestorIds): void {
                $builder->where('category_id', $directCategoryId);

                if ($ancestorIds !== []) {
                    $builder->orWhere(function ($nestedBuilder) use ($ancestorIds): void {
                        $nestedBuilder
                            ->whereIn('category_id', $ancestorIds)
                            ->where('applies_to_children', true);
                    });
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        /** @var Collection<int, EloquentCategoryAttributeDefinition> $definitions */
        $definitions      = $query->get();

        return $definitions->unique(fn(EloquentCategoryAttributeDefinition $definition): string => (string) $definition->id)->values();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function save(array $attributes, ?int $id = null): EloquentCategoryAttributeDefinition
    {
        $definition      = $id !== null
            ? EloquentCategoryAttributeDefinition::query()->findOrFail($id)
            : new EloquentCategoryAttributeDefinition();

        $category        = $this->resolveCategory($attributes['category_id'] ?? null);
        $name            = $this->resolveName($attributes['name'] ?? null);
        $slug            = $this->generateUniqueSlug(
            categoryId: (int) $category->id,
            name: $name,
            slug: $attributes['slug'] ?? null,
            ignoreId: $definition->exists ? (int) $definition->id : null,
        );
        $type            = $this->resolveType($attributes['type'] ?? null);
        $options         = $this->resolveOptions($attributes['options'] ?? null, $type);
        $defaultValue    = $this->resolveDefaultValue($attributes['default_value'] ?? null);
        $dependencyRules = $this->resolveDependencyRules($attributes['dependency_rules'] ?? null);
        $schemaVersion   = $this->resolveSchemaVersion(
            definition: $definition,
            attributes: [
                'type'             => $type->value,
                'options'          => $options,
                'default_value'    => $defaultValue,
                'dependency_rules' => $dependencyRules,
                'is_required'      => (bool) ($attributes['is_required'] ?? false),
                'is_filterable'    => (bool) ($attributes['is_filterable'] ?? false),
                'show_in_card'     => (bool) ($attributes['show_in_card'] ?? false),
            ],
        );

        $definition->fill([
            'category_id'         => $category->id,
            'name'                => $name,
            'slug'                => $slug,
            'type'                => $type,
            'unit'                => $this->resolveNullableString($attributes['unit'] ?? null, 32),
            'description'         => $this->resolveNullableString($attributes['description'] ?? null),
            'placeholder'         => $this->resolveNullableString($attributes['placeholder'] ?? null, 255),
            'help_text'           => $this->resolveNullableString($attributes['help_text'] ?? null),
            'default_value'       => $defaultValue,
            'dependency_rules'    => $dependencyRules,
            'group_name'          => $this->resolveNullableString($attributes['group_name'] ?? null, 120),
            'options'             => $options,
            'is_required'         => (bool) ($attributes['is_required'] ?? false),
            'is_filterable'       => (bool) ($attributes['is_filterable'] ?? false),
            'show_in_card'        => (bool) ($attributes['show_in_card'] ?? false),
            'is_active'           => (bool) ($attributes['is_active'] ?? true),
            'applies_to_children' => (bool) ($attributes['applies_to_children'] ?? true),
            'schema_version'      => $schemaVersion,
            'sort_order'          => $this->resolveSortOrder($attributes['sort_order'] ?? 0),
        ]);
        $definition->save();

        return $definition->fresh('category') ?? $definition;
    }

    public function findById(int $id): ?EloquentCategoryAttributeDefinition
    {
        return EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->find($id);
    }

    public function findByCategoryAndSlug(int $categoryId, string $slug): ?EloquentCategoryAttributeDefinition
    {
        return EloquentCategoryAttributeDefinition::query()
            ->with('category')
            ->where('category_id', $categoryId)
            ->where('slug', $slug)
            ->first();
    }

    public function delete(EloquentCategoryAttributeDefinition $definition): void
    {
        if ($definition->listingValues()->exists()) {
            throw ValidationException::withMessages([
                'attributeDefinitionId' => ['Нельзя удалить характеристику, по которой уже есть значения в объявлениях. Отключите ее вместо удаления.'],
            ]);
        }

        $definition->delete();
    }

    private function resolveCategory(mixed $categoryId): EloquentCategory
    {
        if (! is_numeric($categoryId)) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория характеристики обязательна.'],
            ]);
        }

        $category = EloquentCategory::query()->find((int) $categoryId);

        if ($category === null) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория характеристики не найдена.'],
            ]);
        }

        return $category;
    }

    private function resolveName(mixed $name): string
    {
        $resolvedName = is_string($name) ? trim($name) : '';

        if ($resolvedName === '') {
            throw ValidationException::withMessages([
                'name' => ['Название характеристики обязательно.'],
            ]);
        }

        return Str::limit($resolvedName, 255, '');
    }

    private function resolveType(mixed $type): CategoryAttributeType
    {
        if ($type instanceof CategoryAttributeType) {
            return $type;
        }

        if (is_int($type)) {
            $resolvedType = CategoryAttributeType::tryFrom($type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        if (is_string($type) && is_numeric($type)) {
            $resolvedType = CategoryAttributeType::tryFrom((int) $type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        throw ValidationException::withMessages([
            'type' => ['Укажите корректный тип характеристики.'],
        ]);
    }

    /**
     * @return array<int, string>|null
     */
    private function resolveOptions(mixed $options, CategoryAttributeType $type): ?array
    {
        $supportsOptions   = in_array($type, [CategoryAttributeType::SELECT, CategoryAttributeType::MULTISELECT], true);

        if (! $supportsOptions) {
            return null;
        }

        if ($options === null) {
            return [];
        }

        if (! is_array($options)) {
            throw ValidationException::withMessages([
                'options' => ['Для выбранного типа характеристики нужен массив вариантов.'],
            ]);
        }

        $normalizedOptions = collect($options)
            ->map(static function (mixed $option): ?string {
                if (! is_scalar($option)) {
                    return null;
                }

                $resolvedOption = trim((string) $option);

                return $resolvedOption !== ''
                    ? Str::limit($resolvedOption, 255, '')
                    : null;
            })
            ->filter(static fn(?string $option): bool => $option !== null)
            ->unique()
            ->values()
            ->all();

        return $normalizedOptions;
    }

    private function resolveNullableString(mixed $value, int $limit = 2000): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $resolvedValue = trim($value);

        if ($resolvedValue === '') {
            return null;
        }

        return Str::limit($resolvedValue, $limit, '');
    }

    private function resolveSortOrder(mixed $sortOrder): int
    {
        return is_numeric($sortOrder)
            ? max((int) $sortOrder, 0)
            : 0;
    }

    /**
     * @return array<int, mixed>|array<string, mixed>|null
     */
    private function resolveDefaultValue(mixed $defaultValue): ?array
    {
        return is_array($defaultValue)
            ? $defaultValue
            : null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function resolveDependencyRules(mixed $dependencyRules): ?array
    {
        if ($dependencyRules === null) {
            return null;
        }

        if (! is_array($dependencyRules)) {
            throw ValidationException::withMessages([
                'dependencyRules' => ['Правила зависимости должны быть массивом.'],
            ]);
        }

        $normalizedRules = [];

        foreach (array_values($dependencyRules) as $index => $rule) {
            if (! is_array($rule)) {
                throw ValidationException::withMessages([
                    'dependencyRules.' . $index => ['Каждое правило зависимости должно быть объектом.'],
                ]);
            }

            $operator              = is_string($rule['operator'] ?? null)
                ? trim($rule['operator'])
                : 'equals';

            if (! in_array($operator, ['equals', 'not_equals', 'in', 'not_in', 'filled', 'empty'], true)) {
                throw ValidationException::withMessages([
                    'dependencyRules.' . $index . '.operator' => ['Укажите корректный оператор зависимости.'],
                ]);
            }

            $attributeDefinitionId = $rule['attributeDefinitionId'] ?? $rule['attribute_definition_id'] ?? null;
            $rawAttributeSlug      = $rule['attributeSlug'] ?? $rule['attribute_slug'] ?? null;
            $attributeSlug         = is_string($rawAttributeSlug)
                ? trim($rawAttributeSlug)
                : null;

            if (! is_numeric($attributeDefinitionId) && ($attributeSlug === null || $attributeSlug === '')) {
                throw ValidationException::withMessages([
                    'dependencyRules.' . $index => ['Укажите attributeDefinitionId или attributeSlug для правила зависимости.'],
                ]);
            }

            $normalizedRule        = [
                'operator' => $operator,
            ];

            if (is_numeric($attributeDefinitionId)) {
                $normalizedRule['attributeDefinitionId'] = (int) $attributeDefinitionId;
            }

            if ($attributeSlug !== null && $attributeSlug !== '') {
                $normalizedRule['attributeSlug'] = Str::limit($attributeSlug, 255, '');
            }

            if (array_key_exists('value', $rule)) {
                $normalizedRule['value'] = $rule['value'];
            }

            $normalizedRules[]     = $normalizedRule;
        }

        return $normalizedRules !== [] ? $normalizedRules : null;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function resolveSchemaVersion(
        EloquentCategoryAttributeDefinition $definition,
        array $attributes,
    ): int {
        if (! $definition->exists) {
            return 1;
        }

        foreach ($attributes as $key => $value) {
            $currentValue = $definition->{$key};

            if ($currentValue instanceof CategoryAttributeType) {
                $currentValue = $currentValue->value;
            }

            if ($currentValue != $value) {
                return max((int) $definition->schema_version, 1) + 1;
            }
        }

        return max((int) $definition->schema_version, 1);
    }

    private function generateUniqueSlug(
        int     $categoryId,
        string  $name,
        mixed   $slug,
        ?int    $ignoreId = null,
    ): string {
        $baseSource = is_string($slug) && trim($slug) !== ''
            ? trim($slug)
            : $name;
        $baseSlug   = Str::slug($baseSource);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => ['Не удалось сформировать код характеристики автоматически.'],
            ]);
        }

        $candidate  = $baseSlug;
        $counter    = 2;

        while (
            EloquentCategoryAttributeDefinition::query()
                ->where('category_id', $categoryId)
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
