<?php

declare(strict_types=1);

namespace App\Listing\Application\Support;

use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;

class ListingPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public function map(EloquentListing $listing): array
    {
        $category = $listing->category;

        return [
            'id'             => $listing->id,
            'userId'         => $listing->user_id,
            'category'       => $category === null
                ? null
                : [
                    'id'               => $category->id,
                    'catalogType'      => $category->catalog_type->value,
                    'catalogTypeLabel' => $category->catalog_type->label(),
                    'parentId'         => $category->parent_id,
                    'name'             => $category->name,
                    'slug'             => $category->slug,
                ],
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
            'viewsCount'     => $listing->views_count,
            'isFeatured'     => $listing->is_featured,
            'rejectionReason'=> $listing->rejection_reason,
            'publishedAt'    => $listing->published_at?->toIso8601String(),
            'expiresAt'      => $listing->expires_at?->toIso8601String(),
            'attributeValues'=> $listing->attributeValues
                ->map(
                    fn(EloquentListingAttributeValue $attributeValue): array => [
                        'attributeDefinitionId' => $attributeValue->attribute_definition_id,
                        'name'                  => $attributeValue->attributeDefinition?->name,
                        'slug'                  => $attributeValue->attributeDefinition?->slug,
                        'type'                  => $attributeValue->attributeDefinition?->type?->value,
                        'typeLabel'             => $attributeValue->attributeDefinition?->type?->label(),
                        'value'                 => $attributeValue->value,
                        'displayValue'          => $attributeValue->display_value,
                    ],
                )
                ->values()
                ->all(),
        ];
    }
}
