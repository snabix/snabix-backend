<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Application\Services\CategoryBreadcrumbService;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Collection;

final readonly class ListingPayloadAssembler
{
    public function __construct(
        private CategoryBreadcrumbService $categoryBreadcrumbService,
        private ListingPayloadVisibilityPolicy $visibilityPolicy,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function assemble(
        EloquentListing $listing,
        ListingPayloadVisibility $visibility,
    ): array {
        $media    = $this->imageMedia($listing);
        $location = $this->locationPayload($listing);
        $seller   = $listing->user;
        $payload  = [
            'id'               => $listing->id,
            'userId'           => $listing->user_id,
            'category'         => $this->categoryPayload($listing->category),
            'type'             => $listing->type->value,
            'typeLabel'        => $listing->type->label(),
            'status'           => $listing->status->value,
            'statusLabel'      => $listing->status->label(),
            'condition'        => $listing->condition->value,
            'conditionLabel'   => $listing->condition->label(),
            'title'            => $listing->title,
            'slug'             => $listing->slug,
            'description'      => $listing->description,
            'price'            => $listing->price,
            'currency'         => $listing->currency,
            'isNegotiable'     => $listing->is_negotiable,
            'contactName'      => $listing->contact_name,
            'contactPhone'     => $listing->contact_phone,
            'contactEmail'     => $listing->contact_email,
            'location'         => $location,
            'region'           => $this->locationNestedString($location, 'region', 'name'),
            'city'             => $this->locationNestedString($location, 'city', 'name'),
            'addressLine'      => $this->locationString($location, 'addressLine'),
            'fullLocation'     => $this->locationString($location, 'display'),
            'imageUrl'         => $media->first()?->getFullUrl(),
            'imageUrls'        => $media
                ->map(fn(EloquentMedia $item): string => $item->getFullUrl())
                ->values()
                ->all(),
            'media'            => $this->mediaPayload($media),
            'sellerRating'     => $seller instanceof EloquentUser ? $seller->seller_rating_avg : null,
            'sellerReviewCount'=> $seller instanceof EloquentUser ? $seller->seller_reviews_count : 0,
            'viewsCount'       => $listing->views_count,
            'isFeatured'       => $listing->is_featured,
            'rejectionReason'  => $listing->rejection_reason,
            'publishedAt'      => $listing->published_at?->toIso8601String(),
            'expiresAt'        => $listing->expires_at?->toIso8601String(),
            'attributeValues'  => $this->attributeValuesPayload($listing, $visibility),
        ];

        return $this->visibilityPolicy->apply($payload, $visibility);
    }

    /**
     * @return Collection<int, EloquentMedia>
     */
    private function imageMedia(EloquentListing $listing): Collection
    {
        return $listing->orderedMedia
            ->filter(fn(EloquentMedia $media): bool => $media->media_type === MediaType::IMAGE)
            ->values();
    }

    /**
     * @param  Collection<int, EloquentMedia>                                                $media
     * @return list<array{id: int, url: string, fileName: string, order: int, isMain: bool}>
     */
    private function mediaPayload(Collection $media): array
    {
        return array_values($media
            ->map(fn(EloquentMedia $item, int $index): array => [
                'id'       => $item->id,
                'url'      => $item->getFullUrl(),
                'fileName' => $item->file_name,
                'order'    => (int) ($item->order_column ?? ($index + 1)),
                'isMain'   => $index === 0,
            ])
            ->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    private function categoryPayload(?EloquentCategory $category): ?array
    {
        if ($category === null) {
            return null;
        }

        $breadcrumbTrail = $this->categoryBreadcrumbService->resolve($category);

        return [
            'id'               => $category->id,
            'catalogType'      => $category->catalog_type->value,
            'catalogTypeLabel' => $category->catalog_type->label(),
            'parentId'         => $category->parent_id,
            'name'             => $category->name,
            'slug'             => $category->slug,
            'fullName'         => $breadcrumbTrail['fullName'],
            'path'             => $category->path,
            'breadcrumbs'      => $breadcrumbTrail['breadcrumbs'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function locationPayload(EloquentListing $listing): ?array
    {
        $snapshot    = $listing->address_snapshot;

        if (! is_array($snapshot)) {
            return null;
        }

        $region      = $snapshot['region'] ?? null;

        if (! is_array($region)) {
            return null;
        }

        $regionId    = $region['id'] ?? null;
        $regionName  = $region['name'] ?? null;
        $regionLabel = $region['label'] ?? $regionName;

        if (! is_numeric($regionId) || ! is_string($regionName) || ! is_string($regionLabel)) {
            return null;
        }

        $city        = $this->cityPayload($snapshot['city'] ?? null);

        return [
            'source'           => $this->stringValue($snapshot['source'] ?? null, 'custom'),
            'profileAddressId' => $this->nullableStringValue($snapshot['profileAddressId'] ?? null),
            'label'            => $this->nullableStringValue($snapshot['label'] ?? null),
            'region'           => [
                'id'       => (int) $regionId,
                'name'     => $regionName,
                'fullName' => $this->nullableStringValue($region['fullName'] ?? null),
                'label'    => $regionLabel,
            ],
            'city'             => $city,
            'addressLine'      => $this->nullableStringValue($snapshot['addressLine'] ?? null),
            'display'          => $this->nullableStringValue($snapshot['display'] ?? null),
            'coordinates'      => $this->coordinatesPayload($snapshot['coordinates'] ?? null),
            'mapProvider'      => $this->nullableStringValue($snapshot['mapProvider'] ?? null),
            'mapPlaceId'       => $this->nullableStringValue($snapshot['mapPlaceId'] ?? null),
        ];
    }

    /**
     * @return array{id: int, name: string, label: string, lat?: string|null, lon?: string|null}|null
     */
    private function cityPayload(mixed $city): ?array
    {
        if (! is_array($city)) {
            return null;
        }

        $cityId    = $city['id'] ?? null;
        $cityName  = $city['name'] ?? null;
        $cityLabel = $city['label'] ?? $cityName;

        if (! is_numeric($cityId) || ! is_string($cityName) || ! is_string($cityLabel)) {
            return null;
        }

        return [
            'id'    => (int) $cityId,
            'name'  => $cityName,
            'label' => $cityLabel,
            'lat'   => $this->nullableStringValue($city['lat'] ?? null),
            'lon'   => $this->nullableStringValue($city['lon'] ?? null),
        ];
    }

    /**
     * @return array{lat: int|float|string|null, lng: int|float|string|null}
     */
    private function coordinatesPayload(mixed $coordinates): array
    {
        if (! is_array($coordinates)) {
            return [
                'lat' => null,
                'lng' => null,
            ];
        }

        return [
            'lat' => $this->nullableCoordinateValue($coordinates['lat'] ?? null),
            'lng' => $this->nullableCoordinateValue($coordinates['lng'] ?? null),
        ];
    }

    private function nullableCoordinateValue(mixed $value): int | float | string | null
    {
        return is_int($value) || is_float($value) || is_string($value) ? $value : null;
    }

    private function stringValue(mixed $value, string $fallback): string
    {
        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function nullableStringValue(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
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

    /**
     * @return list<array<string, mixed>>
     */
    private function attributeValuesPayload(
        EloquentListing $listing,
        ListingPayloadVisibility $visibility,
    ): array {
        return array_values($listing->attributeValues
            ->filter(
                fn(EloquentListingAttributeValue $attributeValue): bool => $this->visibilityPolicy
                    ->includesAttributeValue($attributeValue, $visibility),
            )
            ->map(fn(EloquentListingAttributeValue $attributeValue): array => [
                'attributeDefinitionId' => $attributeValue->attribute_definition_id,
                'schemaVersion'         => $attributeValue->attribute_schema_version,
                'name'                  => $this->attributeSnapshotValue($attributeValue, 'name', $attributeValue->attributeDefinition?->name),
                'slug'                  => $this->attributeSnapshotValue($attributeValue, 'slug', $attributeValue->attributeDefinition?->slug),
                'type'                  => $this->attributeSnapshotValue($attributeValue, 'type', $attributeValue->attributeDefinition?->type?->value),
                'typeLabel'             => $this->attributeSnapshotValue($attributeValue, 'typeLabel', $attributeValue->attributeDefinition?->type?->label()),
                'value'                 => $attributeValue->value,
                'displayValue'          => $attributeValue->display_value,
            ])
            ->all());
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
