<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCases\ImportCategoryAttributeDefinitions;

use App\Catalog\Application\Support\CategoryAttributeDefinitionPayloadMapper;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

readonly class ImportCategoryAttributeDefinitionsHandler
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $repository,
        private CategoryAttributeDefinitionPayloadMapper $payloadMapper,
    ) {}

    public function execute(ImportCategoryAttributeDefinitionsInput $input): ImportCategoryAttributeDefinitionsOutput
    {
        $created = 0;
        $updated = 0;
        $items   = [];

        DB::transaction(function () use ($input, &$created, &$updated, &$items): void {
            foreach ($input->items as $index => $item) {
                $attributes = $this->normalizeItem($item, $index);
                $existing   = $this->resolveExistingDefinition($attributes);
                $definition = $this->repository->save($attributes, $existing?->id);

                if ($existing === null) {
                    $created++;
                } else {
                    $updated++;
                }

                $items[]    = $this->payloadMapper->map($definition);
            }
        });

        return ImportCategoryAttributeDefinitionsOutput::from([
            'created' => $created,
            'updated' => $updated,
            'items'   => $items,
        ]);
    }

    /**
     * @param  array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function normalizeItem(array $item, int $index): array
    {
        $categoryId = $item['categoryId'] ?? $item['category_id'] ?? null;
        $name       = $item['name'] ?? null;
        $slug       = $item['slug'] ?? null;
        $type       = $item['type'] ?? null;
        $sortOrder  = $item['sortOrder'] ?? $item['sort_order'] ?? null;

        if (! is_numeric($categoryId) || ! is_string($name) || ! is_numeric($type)) {
            throw ValidationException::withMessages([
                'items.' . $index => ['Для импорта характеристики нужны categoryId, name и type.'],
            ]);
        }

        return [
            'category_id'         => intval($categoryId),
            'name'                => $name,
            'slug'                => is_string($slug) && trim($slug) !== '' ? Str::slug($slug) : null,
            'type'                => intval($type),
            'unit'                => $item['unit'] ?? null,
            'description'         => $item['description'] ?? null,
            'placeholder'         => $item['placeholder'] ?? null,
            'help_text'           => $item['helpText'] ?? $item['help_text'] ?? null,
            'default_value'       => $item['defaultValue'] ?? $item['default_value'] ?? null,
            'dependency_rules'    => $item['dependencyRules'] ?? $item['dependency_rules'] ?? null,
            'group_name'          => $item['groupName'] ?? $item['group_name'] ?? null,
            'options'             => $item['options'] ?? null,
            'is_required'         => (bool) ($item['isRequired'] ?? $item['is_required'] ?? false),
            'is_filterable'       => (bool) ($item['isFilterable'] ?? $item['is_filterable'] ?? false),
            'show_in_card'        => (bool) ($item['showInCard'] ?? $item['show_in_card'] ?? false),
            'is_active'           => (bool) ($item['isActive'] ?? $item['is_active'] ?? true),
            'applies_to_children' => (bool) ($item['appliesToChildren'] ?? $item['applies_to_children'] ?? true),
            'sort_order'          => is_numeric($sortOrder)
                ? intval($sortOrder)
                : 0,
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function resolveExistingDefinition(array $attributes): ?EloquentCategoryAttributeDefinition
    {
        $slug       = $attributes['slug'];
        $name       = $attributes['name'];

        if (! is_string($slug) || $slug === '') {
            $slug = is_string($name) ? Str::slug($name) : '';
        }

        $categoryId = $attributes['category_id'];

        if (! is_numeric($categoryId)) {
            return null;
        }

        return $this->repository->findByCategoryAndSlug(intval($categoryId), $slug);
    }
}
