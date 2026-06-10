<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;

class ListingPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(EloquentListing $listing): array
    {
        $category = $listing->category;

        $media    = $listing->orderedMedia
            ->filter(fn(EloquentMedia $media): bool => $media->media_type === MediaType::IMAGE)
            ->values();
        $location = $this->locationPayload($listing);

        return [
            'id'             => $listing->id,
            'userId'         => $listing->user_id,
            'category'       => $this->categoryPayload($category),
            'type'           => $listing->type->value,
            'typeLabel'      => $listing->type->label(),
            'status'         => $listing->status->value,
            'statusLabel'    => $listing->status->label(),
            'condition'      => $listing->condition->value,
            'conditionLabel' => $listing->condition->label(),
            'title'          => $listing->title,
            'slug'           => $listing->slug,
            'description'    => $listing->description,
            'price'          => $listing->price,
            'currency'       => $listing->currency,
            'isNegotiable'   => $listing->is_negotiable,
            'contactName'    => $listing->contact_name,
            'contactPhone'   => $listing->contact_phone,
            'contactEmail'   => $listing->contact_email,
            'location'       => $location,
            'region'         => $this->locationNestedString($location, 'region', 'name'),
            'city'           => $this->locationNestedString($location, 'city', 'name'),
            'addressLine'    => $this->locationString($location, 'addressLine'),
            'fullLocation'   => $this->locationString($location, 'display'),
            'imageUrl'       => $media->first()?->getFullUrl(),
            'imageUrls'      => $media
                ->map(fn(EloquentMedia $media): string => $media->getFullUrl())
                ->values()
                ->all(),
            'media'          => $media
                ->map(fn(EloquentMedia $media, int $index): array => [
                    'id'       => $media->id,
                    'url'      => $media->getFullUrl(),
                    'fileName' => $media->file_name,
                    'order'    => (int) ($media->order_column ?? ($index + 1)),
                    'isMain'   => $index === 0,
                ])
                ->values()
                ->all(),
            'viewsCount'     => $listing->views_count,
            'isFeatured'     => $listing->is_featured,
            'rejectionReason'=> $listing->rejection_reason,
            'publishedAt'    => $listing->published_at?->toIso8601String(),
            'expiresAt'      => $listing->expires_at?->toIso8601String(),
            'attributeValues'=> $listing->attributeValues
                ->map(
                    fn(EloquentListingAttributeValue $attributeValue): array => [
                        'attributeDefinitionId' => $attributeValue->attribute_definition_id,
                        'schemaVersion'         => $attributeValue->attribute_schema_version,
                        'name'                  => $this->attributeSnapshotValue($attributeValue, 'name', $attributeValue->attributeDefinition?->name),
                        'slug'                  => $this->attributeSnapshotValue($attributeValue, 'slug', $attributeValue->attributeDefinition?->slug),
                        'type'                  => $this->attributeSnapshotValue($attributeValue, 'type', $attributeValue->attributeDefinition?->type?->value),
                        'typeLabel'             => $this->attributeSnapshotValue($attributeValue, 'typeLabel', $attributeValue->attributeDefinition?->type?->label()),
                        'value'                 => $attributeValue->value,
                        'displayValue'          => $attributeValue->display_value,
                    ],
                )
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function categoryPayload(?EloquentCategory $category): ?array
    {
        if ($category === null) {
            return null;
        }

        return [
            'id'               => $category->id,
            'catalogType'      => $category->catalog_type->value,
            'catalogTypeLabel' => $category->catalog_type->label(),
            'parentId'         => $category->parent_id,
            'name'             => $category->name,
            'slug'             => $category->slug,
            'fullName'         => $category->full_name,
            'path'             => $category->path,
            'breadcrumbs'      => $this->categoryBreadcrumbs($category),
        ];
    }

    /**
     * @return list<array{id: string, name: string, slug: string}>
     */
    private function categoryBreadcrumbs(EloquentCategory $category): array
    {
        $breadcrumbs = [];
        $current     = $category;

        while ($current instanceof EloquentCategory) {
            array_unshift($breadcrumbs, [
                'id'   => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
            ]);

            $current = $current->parentCategory()->first();
        }

        return $breadcrumbs;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function locationPayload(EloquentListing $listing): ?array
    {
        $snapshot = $listing->address_snapshot;

        return is_array($snapshot) ? $snapshot : null;
    }

    /**
     * @param array<string, mixed>|null $location
     */
    private function locationString(?array $location, string $key): ?string
    {
        $value = $location[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed>|null $location
     */
    private function locationNestedString(?array $location, string $parentKey, string $key): ?string
    {
        $parent = $location[$parentKey] ?? null;

        if (! is_array($parent)) {
            return null;
        }

        $value  = $parent[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    private function attributeSnapshotValue(
        EloquentListingAttributeValue $attributeValue,
        string $key,
        mixed $fallback,
    ): mixed {
        $snapshot = $attributeValue->attribute_snapshot;

        return is_array($snapshot) && array_key_exists($key, $snapshot)
            ? $snapshot[$key]
            : $fallback;
    }
}
