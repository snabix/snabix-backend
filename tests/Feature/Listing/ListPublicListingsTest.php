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
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.title', 'Горный велосипед')
            ->assertJsonPath('data.meta.currentPage', 1)
            ->assertJsonPath('data.meta.perPage', 24)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonMissingPath('data.items.0.userId')
            ->assertJsonMissingPath('data.items.0.contactName')
            ->assertJsonMissingPath('data.items.0.contactPhone')
            ->assertJsonMissingPath('data.items.0.contactEmail');
    }

    public function test_public_listings_are_paginated(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Самокаты',
            'slug'         => 'samokaty',
            'catalog_type' => 1,
        ]);

        $firstListing       = $this->createPublishedListing($user->id, $category->id, 'Первый самокат', 'pervyj-samokat', now()->subDay());
        $secondListing      = $this->createPublishedListing($user->id, $category->id, 'Второй самокат', 'vtoroj-samokat', now());

        $this
            ->getJson('/api/v1/public/listings?perPage=1&page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $secondListing->id)
            ->assertJsonPath('data.meta.currentPage', 1)
            ->assertJsonPath('data.meta.perPage', 1)
            ->assertJsonPath('data.meta.total', 2);

        $this
            ->getJson('/api/v1/public/listings?perPage=1&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $firstListing->id)
            ->assertJsonPath('data.meta.currentPage', 2)
            ->assertJsonPath('data.meta.perPage', 1)
            ->assertJsonPath('data.meta.total', 2);
    }

    private function createPublishedListing(
        string $userId,
        int $categoryId,
        string $title,
        string $slug,
        mixed $publishedAt,
    ): EloquentListing {
        return EloquentListing::query()->create([
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => $title,
            'slug'          => $slug,
            'description'   => 'Публичное объявление.',
            'price'         => 30000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
            'published_at'  => $publishedAt,
        ]);
    }
}
