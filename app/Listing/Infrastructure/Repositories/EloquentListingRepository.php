<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Repositories;

use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

readonly class EloquentListingRepository implements ListingRepositoryInterface
{
    public function __construct(
        private CategoryAttributeDefinitionRepositoryInterface $categoryAttributeDefinitionRepository,
    ) {}

    /**
     * @return Collection<int, EloquentListing>
     */
    public function listOwnedByUser(string $userId): Collection
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition'])
            ->where('user_id', $userId)
            ->latest('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, EloquentListing>
     */
    public function listPublicPublished(int $limit = 24): Collection
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition'])
            ->where('status', ListingStatus::PUBLISHED)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->latest('created_at')
            ->limit(max($limit, 1))
            ->get();
    }

    /**
     * @param  array<string, mixed>    $attributes
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function create(
        array $attributes,
        array $attributeValues = [],
        bool $validateRequiredAttributes = true,
    ): EloquentListing {
        /** @var EloquentListing $listing */
        $listing = DB::transaction(function () use ($attributes, $attributeValues, $validateRequiredAttributes): EloquentListing {
            $category   = $this->resolveCategory($attributes['category_id'] ?? null);
            $type       = $this->resolveType($attributes['type'] ?? null);
            $condition  = $this->resolveCondition($attributes['condition'] ?? null, $type);
            $status     = $this->resolveStatus($attributes['status'] ?? null);
            $title      = $this->resolveTitle($attributes['title'] ?? null);
            $slug       = $this->generateUniqueSlug($title);
            $userId     = $this->resolveUserId($attributes['user_id'] ?? null);

            $this->assertTypeMatchesCategory($type, $category);

            $listing    = EloquentListing::query()->create([
                'user_id'          => $userId,
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $status,
                'condition'        => $condition,
                'title'            => $title,
                'slug'             => $slug,
                'description'      => $this->resolveDescription($attributes['description'] ?? null),
                'price'            => $this->resolvePrice($attributes['price'] ?? null),
                'currency'         => $this->resolveCurrency($attributes['currency'] ?? null),
                'is_negotiable'    => (bool) ($attributes['is_negotiable'] ?? false),
                'contact_name'     => $this->resolveNullableString($attributes['contact_name'] ?? null, 120),
                'contact_phone'    => $this->resolveNullableString($attributes['contact_phone'] ?? null, 32),
                'contact_email'    => $this->resolveEmail($attributes['contact_email'] ?? null),
                'views_count'      => $this->resolveViewsCount($attributes['views_count'] ?? 0),
                'is_featured'      => (bool) ($attributes['is_featured'] ?? false),
                'rejection_reason' => $this->resolveNullableString($attributes['rejection_reason'] ?? null, 5000),
                'published_at'     => null,
                'expires_at'       => null,
            ]);

            $this->syncAttributeValues(
                listing: $listing,
                categoryId: $category->id,
                attributeValues: $attributeValues,
                validateRequiredAttributes: $validateRequiredAttributes,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition']) ?? $listing;
        });

        return $listing;
    }

    /**
     * @param  array<string, mixed>    $attributes
     * @param  array<array-key, mixed> $attributeValues
     * @throws Throwable
     */
    public function update(
        EloquentListing $listing,
        array $attributes,
        array $attributeValues = [],
        bool $validateRequiredAttributes = true,
    ): EloquentListing {
        /** @var EloquentListing $updatedListing */
        $updatedListing = DB::transaction(function () use ($listing, $attributes, $attributeValues, $validateRequiredAttributes): EloquentListing {
            $category  = $this->resolveCategory($attributes['category_id'] ?? $listing->category_id);
            $type      = $this->resolveType($attributes['type'] ?? $listing->type);
            $condition = $this->resolveCondition($attributes['condition'] ?? $listing->condition, $type);
            $status    = $this->resolveStatus($attributes['status'] ?? $listing->status);
            $title     = $this->resolveTitle($attributes['title'] ?? $listing->title);

            $this->assertTypeMatchesCategory($type, $category);

            $listing->fill([
                'category_id'      => $category->id,
                'type'             => $type,
                'status'           => $status,
                'condition'        => $condition,
                'title'            => $title,
                'slug'             => $this->generateUniqueSlug($title, $listing->id),
                'description'      => $this->resolveDescription($attributes['description'] ?? $listing->description),
                'price'            => $this->resolvePrice($attributes['price'] ?? $listing->price),
                'currency'         => $this->resolveCurrency($attributes['currency'] ?? $listing->currency),
                'is_negotiable'    => (bool) ($attributes['is_negotiable'] ?? $listing->is_negotiable),
                'contact_name'     => $this->resolveNullableString($attributes['contact_name'] ?? $listing->contact_name, 120),
                'contact_phone'    => $this->resolveNullableString($attributes['contact_phone'] ?? $listing->contact_phone, 32),
                'contact_email'    => $this->resolveEmail($attributes['contact_email'] ?? $listing->contact_email),
                'views_count'      => $this->resolveViewsCount($attributes['views_count'] ?? $listing->views_count),
                'is_featured'      => (bool) ($attributes['is_featured'] ?? $listing->is_featured),
                'rejection_reason' => $this->resolveNullableString($attributes['rejection_reason'] ?? $listing->rejection_reason, 5000),
            ]);
            $listing->save();

            $this->syncAttributeValues(
                listing: $listing,
                categoryId: $category->id,
                attributeValues: $attributeValues,
                validateRequiredAttributes: $validateRequiredAttributes,
            );

            return $listing->fresh(['category', 'attributeValues.attributeDefinition']) ?? $listing;
        });

        return $updatedListing;
    }

    public function findOwnedByUser(string $listingId, string $userId): ?EloquentListing
    {
        return EloquentListing::query()
            ->with(['category', 'attributeValues.attributeDefinition'])
            ->whereKey($listingId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @throws Throwable
     */
    public function delete(EloquentListing $listing): void
    {
        DB::transaction(function () use ($listing): void {
            $listing->attributeValues()->delete();
            $listing->delete();
        });
    }

    private function resolveCategory(mixed $categoryId): EloquentCategory
    {
        if (! is_numeric($categoryId)) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория объявления обязательна.'],
            ]);
        }

        $category = EloquentCategory::query()->find((int) $categoryId);

        if ($category === null) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория не найдена.'],
            ]);
        }

        return $category;
    }

    private function resolveType(mixed $type): ListingType
    {
        if ($type instanceof ListingType) {
            return $type;
        }

        if (is_int($type)) {
            $resolvedType = ListingType::tryFrom($type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        if (is_string($type) && is_numeric($type)) {
            $resolvedType = ListingType::tryFrom((int) $type);

            if ($resolvedType !== null) {
                return $resolvedType;
            }
        }

        throw ValidationException::withMessages([
            'type' => ['Укажите корректный тип объявления.'],
        ]);
    }

    private function resolveCondition(mixed $condition, ListingType $type): ListingCondition
    {
        if ($type === ListingType::SERVICE) {
            return ListingCondition::NOT_APPLICABLE;
        }

        if ($condition instanceof ListingCondition) {
            return $condition;
        }

        if (is_int($condition)) {
            $resolvedCondition = ListingCondition::tryFrom($condition);

            if ($resolvedCondition !== null) {
                return $resolvedCondition;
            }
        }

        if (is_string($condition) && is_numeric($condition)) {
            $resolvedCondition = ListingCondition::tryFrom((int) $condition);

            if ($resolvedCondition !== null) {
                return $resolvedCondition;
            }
        }

        return ListingCondition::USED;
    }

    private function resolveStatus(mixed $status): ListingStatus
    {
        if ($status instanceof ListingStatus) {
            return $status;
        }

        if (is_int($status)) {
            return ListingStatus::tryFrom($status) ?? ListingStatus::DRAFT;
        }

        if (is_string($status) && is_numeric($status)) {
            return ListingStatus::tryFrom((int) $status) ?? ListingStatus::DRAFT;
        }

        return ListingStatus::DRAFT;
    }

    private function resolveTitle(mixed $title): string
    {
        $resolvedTitle = is_string($title) ? trim($title) : '';

        if ($resolvedTitle === '') {
            throw ValidationException::withMessages([
                'title' => ['Заголовок объявления обязателен.'],
            ]);
        }

        return $resolvedTitle;
    }

    private function resolveDescription(mixed $description): string
    {
        $resolvedDescription = is_string($description) ? trim($description) : '';

        if ($resolvedDescription === '') {
            throw ValidationException::withMessages([
                'description' => ['Описание объявления обязательно.'],
            ]);
        }

        return $resolvedDescription;
    }

    private function resolvePrice(mixed $price): ?int
    {
        if ($price === null || $price === '') {
            return null;
        }

        if (is_int($price)) {
            return $price;
        }

        if (is_string($price) && preg_match('/^\d+$/', $price) === 1) {
            return (int) $price;
        }

        if (is_float($price) && floor($price) === $price) {
            return (int) $price;
        }

        if (is_string($price) && preg_match('/^\d+\.0+$/', $price) === 1) {
            return (int) $price;
        }

        if (! is_numeric($price)) {
            throw ValidationException::withMessages([
                'price' => ['Цена должна быть целым числом.'],
            ]);
        }

        throw ValidationException::withMessages([
            'price' => ['Цена должна быть целым числом.'],
        ]);
    }

    private function resolveCurrency(mixed $currency): string
    {
        $resolvedCurrency = is_string($currency) ? strtoupper(trim($currency)) : 'RUB';

        if ($resolvedCurrency === '') {
            return 'RUB';
        }

        return Str::limit($resolvedCurrency, 3, '');
    }

    private function resolveUserId(mixed $userId): string
    {
        if (! is_string($userId) || trim($userId) === '') {
            throw ValidationException::withMessages([
                'userId' => ['Пользователь объявления не определён.'],
            ]);
        }

        return $userId;
    }

    private function resolveNullableString(
        mixed $value,
        int $limit = 255,
    ): ?string {
        if (! is_string($value)) {
            return null;
        }

        $resolvedValue = trim($value);

        if ($resolvedValue === '') {
            return null;
        }

        return Str::limit($resolvedValue, $limit, '');
    }

    private function resolveEmail(mixed $email): ?string
    {
        $resolvedEmail = $this->resolveNullableString($email);

        if ($resolvedEmail === null) {
            return null;
        }

        return mb_strtolower($resolvedEmail);
    }

    private function resolveViewsCount(mixed $viewsCount): int
    {
        return is_numeric($viewsCount)
            ? max((int) $viewsCount, 0)
            : 0;
    }

    private function assertTypeMatchesCategory(ListingType $type, EloquentCategory $category): void
    {
        $categoryType = $category->catalog_type;

        if (
            ($type === ListingType::PRODUCT && $categoryType !== CategoryCatalogType::PRODUCT)
            || ($type === ListingType::SERVICE && $categoryType !== CategoryCatalogType::SERVICE)
        ) {
            throw ValidationException::withMessages([
                'categoryId' => ['Категория не соответствует выбранному типу объявления.'],
            ]);
        }
    }

    private function generateUniqueSlug(
        string $title,
        ?string $ignoreId = null,
    ): string {
        $baseSlug  = Str::slug($title);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => ['Не удалось сформировать slug для объявления.'],
            ]);
        }

        $candidate = $baseSlug;
        $counter   = 2;

        while (
            EloquentListing::query()
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    /**
     * @param array<array-key, mixed> $attributeValues
     */
    private function syncAttributeValues(
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

    private function deleteAttributeValue(EloquentListing $listing, int $definitionId): void
    {
        $listing
            ->attributeValues()
            ->where('attribute_definition_id', $definitionId)
            ->delete();
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

        $options           = $this->normalizedDefinitionOptions($definition);
        $normalizedValues  = collect($value)
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
