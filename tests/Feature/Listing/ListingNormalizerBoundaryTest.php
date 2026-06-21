<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Application\Normalizers\ListingCreateNormalizer;
use App\Listing\Application\Normalizers\ListingUpdateNormalizer;
use App\Listing\Application\UseCases\CreateListing\CreateListingInput;
use App\Listing\Application\UseCases\UpdateListing\UpdateListingInput;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use Tests\Feature\FeatureTestCase;

class ListingNormalizerBoundaryTest extends FeatureTestCase
{
    public function test_create_normalizer_assigns_safe_moderation_defaults(): void
    {
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Станки',
            'slug'         => 'stanki-normalizer-create',
            'catalog_type' => ListingType::PRODUCT->value,
        ]);

        $data     = app(ListingCreateNormalizer::class)->normalize(
            new CreateListingInput(
                userId: 'user-id',
                categoryId: $category->id,
                type: ListingType::PRODUCT->value,
                condition: ListingCondition::USED->value,
                title: '  Токарный станок  ',
                description: '  Рабочее состояние.  ',
                price: 150000,
                currency: 'rub',
                isNegotiable: true,
                contactName: '  Иван  ',
                contactPhone: null,
                contactEmail: '  USER@EXAMPLE.COM  ',
                addressMode: 'none',
                profileAddressId: null,
                regionId: null,
                cityId: null,
                addressLine: null,
                saveAsDraft: false,
                attributeValues: [],
            ),
            ListingStatus::PENDING_REVIEW,
            [],
        );

        $this->assertSame('Токарный станок', $data->attributes['title']);
        $this->assertSame('RUB', $data->attributes['currency']);
        $this->assertSame('user@example.com', $data->attributes['contact_email']);
        $this->assertSame(ListingStatus::PENDING_REVIEW, $data->attributes['status']);
        $this->assertSame(0, $data->attributes['views_count']);
        $this->assertFalse($data->attributes['is_featured']);
        $this->assertNull($data->attributes['rejection_reason']);
    }

    public function test_update_normalizer_cannot_emit_moderation_fields(): void
    {
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Компрессоры',
            'slug'         => 'kompressory-normalizer-update',
            'catalog_type' => ListingType::PRODUCT->value,
        ]);

        $data     = app(ListingUpdateNormalizer::class)->normalize(
            new UpdateListingInput(
                userId: 'user-id',
                listingId: 'listing-id',
                categoryId: $category->id,
                type: ListingType::PRODUCT->value,
                condition: ListingCondition::USED->value,
                title: 'Компрессор',
                description: 'Исправен.',
                price: 45000,
                currency: 'RUB',
                isNegotiable: false,
                contactName: null,
                contactPhone: null,
                contactEmail: null,
                addressMode: 'none',
                profileAddressId: null,
                regionId: null,
                cityId: null,
                addressLine: null,
                attributeValues: [],
            ),
            [],
        );

        foreach ([
            'status',
            'views_count',
            'is_featured',
            'rejection_reason',
            'published_at',
            'expires_at',
        ] as $field) {
            $this->assertArrayNotHasKey($field, $data->attributes);
        }
    }
}
