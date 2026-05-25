<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Application\Services\ListingMediaService;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertJsonPath('data.meta.perPage', 15)
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

    public function test_public_listings_include_image_urls(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $categoryRepository = app(CategoryRepositoryInterface::class);
        $mediaService       = app(ListingMediaService::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Фототовары',
            'slug'         => 'fototovary',
            'catalog_type' => 1,
        ]);
        $listing            = $this->createPublishedListing(
            $user->id,
            $category->id,
            'Фотоаппарат',
            'fotoapparat',
            now(),
        );

        $mediaService->uploadImages($listing, [$this->fakePng('listing-photo.png')]);

        $this
            ->getJson('/api/v1/public/listings')
            ->assertOk()
            ->assertJsonCount(1, 'data.items.0.imageUrls')
            ->assertJsonPath('data.items.0.imageUrl', fn(mixed $url): bool => is_string($url) && $url !== '');
    }

    public function test_public_listings_can_be_filtered_and_sorted(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $rootCategory       = $categoryRepository->save([
            'name'         => 'Электроника',
            'slug'         => 'elektronika',
            'catalog_type' => 1,
        ]);
        $childCategory      = $categoryRepository->save([
            'parent_id'    => $rootCategory->id,
            'name'         => 'Ноутбуки',
            'slug'         => 'noutbuki',
            'catalog_type' => 1,
        ]);
        $otherCategory      = $categoryRepository->save([
            'name'         => 'Услуги ремонта',
            'slug'         => 'uslugi-remonta',
            'catalog_type' => 2,
        ]);

        $matchedListing     = $this->createPublishedListing(
            $user->id,
            $childCategory->id,
            'Ноутбук игровой',
            'noutbuk-igrovoj',
            now(),
            [
                'price' => 85000,
            ],
        );

        $this->createPublishedListing(
            $user->id,
            $rootCategory->id,
            'Старый монитор',
            'staryj-monitor',
            now()->subDay(),
            [
                'price' => 10000,
            ],
        );

        $this->createPublishedListing(
            $user->id,
            $otherCategory->id,
            'Ремонт ноутбуков',
            'remont-noutbukov',
            now(),
            [
                'type'  => ListingType::SERVICE,
                'price' => 5000,
            ],
        );

        $this
            ->getJson(sprintf(
                '/api/v1/public/listings?categoryId=%d&type=%d&minPrice=80000&maxPrice=90000&sort=price_desc',
                $rootCategory->id,
                ListingType::PRODUCT->value,
            ))
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $matchedListing->id);
    }

    public function test_public_listings_validate_filter_values(): void
    {
        $this
            ->getJson('/api/v1/public/listings?minPrice=90000&maxPrice=1000&sort=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['maxPrice', 'sort']);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPublishedListing(
        string $userId,
        int $categoryId,
        string $title,
        string $slug,
        mixed $publishedAt,
        array $overrides = [],
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
            ...$overrides,
        ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true) ?: '',
        );
    }
}
