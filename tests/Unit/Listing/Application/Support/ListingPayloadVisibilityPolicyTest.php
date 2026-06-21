<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Application\Support;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Application\Support\ListingPayloadVisibility;
use App\Listing\Application\Support\ListingPayloadVisibilityPolicy;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use PHPUnit\Framework\TestCase;

class ListingPayloadVisibilityPolicyTest extends TestCase
{
    public function test_public_view_uses_an_explicit_allowlist(): void
    {
        $policy  = new ListingPayloadVisibilityPolicy();
        $payload = [
            'id'            => 'listing-id',
            'title'         => 'Публичное объявление',
            'userId'        => 'private-user-id',
            'contactEmail'  => 'private@example.com',
            'futureSecret'  => 'must-not-leak',
        ];

        $this->assertSame([
            'id'    => 'listing-id',
            'title' => 'Публичное объявление',
        ], $policy->apply($payload, ListingPayloadVisibility::PUBLIC_VIEW));
        $this->assertSame(
            $payload,
            $policy->apply($payload, ListingPayloadVisibility::PRIVATE_VIEW),
        );
    }

    public function test_public_view_includes_only_card_attribute_values(): void
    {
        $policy                   = new ListingPayloadVisibilityPolicy();
        $attributeValue           = new EloquentListingAttributeValue();
        $definition               = new EloquentCategoryAttributeDefinition();
        $definition->show_in_card = false;
        $attributeValue->setRelation('attributeDefinition', $definition);

        $this->assertTrue($policy->includesAttributeValue(
            $attributeValue,
            ListingPayloadVisibility::PRIVATE_VIEW,
        ));
        $this->assertFalse($policy->includesAttributeValue(
            $attributeValue,
            ListingPayloadVisibility::PUBLIC_VIEW,
        ));

        $definition->show_in_card = true;

        $this->assertTrue($policy->includesAttributeValue(
            $attributeValue,
            ListingPayloadVisibility::PUBLIC_VIEW,
        ));
    }

    public function test_public_allowlist_never_contains_private_fields(): void
    {
        $publicFields = (new ListingPayloadVisibilityPolicy())->publicFields();

        foreach ([
            'userId',
            'contactName',
            'contactPhone',
            'contactEmail',
            'rejectionReason',
            'media',
        ] as $privateField) {
            $this->assertNotContains($privateField, $publicFields);
        }
    }
}
