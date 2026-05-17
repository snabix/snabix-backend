<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Tests\Feature\FeatureTestCase;

class ListPublicListingsTest extends FeatureTestCase
{
    public function test_public_listings_do_not_expose_owner_or_contact_fields(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Велосипеды',
            'slug'         => 'velosipedy',
            'catalog_type' => 1,
        ]);

        EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => 'Горный велосипед',
            'slug'          => 'gornyj-velosiped',
            'description'   => 'Велосипед после обслуживания.',
            'price'         => 30000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
            'contact_name'  => 'Private Name',
            'contact_phone' => '+79990000001',
            'contact_email' => 'private@example.com',
            'published_at'  => now(),
        ]);

        $this
            ->getJson('/api/v1/public/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Горный велосипед')
            ->assertJsonMissingPath('data.0.userId')
            ->assertJsonMissingPath('data.0.contactName')
            ->assertJsonMissingPath('data.0.contactPhone')
            ->assertJsonMissingPath('data.0.contactEmail');
    }
}
