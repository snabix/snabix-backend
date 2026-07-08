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
use App\Location\Infrastructure\Models\EloquentCity;
use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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
            ->assertJsonMissingPath('data.items.0.contactEmail')
            ->assertJsonMissingPath('data.items.0.rejectionReason')
            ->assertJsonMissingPath('data.items.0.media');
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
                '/api/v1/public/listings?categoryId=%s&type=%d&minPrice=80000&maxPrice=90000&sort=price_desc',
                $rootCategory->id,
                ListingType::PRODUCT->value,
            ))
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $matchedListing->id);
    }

    public function test_public_listings_can_be_filtered_by_negotiable_price(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Стройматериалы',
            'slug'         => 'strojmaterialy',
            'catalog_type' => 1,
        ]);

        $negotiableListing  = $this->createPublishedListing(
            $user->id,
            $category->id,
            'Кирпич с торгом',
            'kirpich-s-torgom',
            now(),
            [
                'is_negotiable' => true,
            ],
        );
        $fixedPriceListing  = $this->createPublishedListing(
            $user->id,
            $category->id,
            'Кирпич без торга',
            'kirpich-bez-torga',
            now()->subMinute(),
            [
                'is_negotiable' => false,
            ],
        );

        $this
            ->getJson('/api/v1/public/listings?isNegotiable=true')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $negotiableListing->id)
            ->assertJsonPath('data.items.0.isNegotiable', true);

        $this
            ->getJson('/api/v1/public/listings?isNegotiable=false')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $fixedPriceListing->id)
            ->assertJsonPath('data.items.0.isNegotiable', false);
    }

    public function test_public_listings_can_be_filtered_by_region_and_city(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Инструменты',
            'slug'         => 'instrumenty',
            'catalog_type' => 1,
        ]);
        $region             = EloquentRegion::query()->create([
            'kladr_id'     => '2300000000000',
            'name'         => 'Краснодарский край',
            'slug'         => 'krasnodarskij-kraj',
            'label'        => 'Краснодарский край',
            'content_type' => 'region',
            'is_active'    => true,
            'sort_order'   => 1,
        ]);
        $city               = EloquentCity::query()->create([
            'region_id'    => $region->id,
            'kladr_id'     => '2300000100000',
            'name'         => 'Краснодар',
            'slug'         => 'krasnodar',
            'label'        => 'Краснодар',
            'content_type' => 'city',
            'is_active'    => true,
            'sort_order'   => 1,
        ]);
        $otherRegion        = EloquentRegion::query()->create([
            'kladr_id'     => '7700000000000',
            'name'         => 'Москва',
            'slug'         => 'moskva',
            'label'        => 'Москва',
            'content_type' => 'region',
            'is_active'    => true,
            'sort_order'   => 2,
        ]);

        $matchedListing     = $this->createPublishedListing(
            $user->id,
            $category->id,
            'Перфоратор в Краснодаре',
            'perforator-v-krasnodare',
            now(),
            [
                'region_id'        => $region->id,
                'city_id'          => $city->id,
                'address_snapshot' => [
                    'region' => [
                        'id'       => $region->id,
                        'name'     => $region->name,
                        'fullName' => $region->fullname ?? $region->name,
                        'label'    => $region->label,
                    ],
                    'city'   => [
                        'id'    => $city->id,
                        'name'  => $city->name,
                        'label' => $city->label,
                    ],
                ],
            ],
        );

        $this->createPublishedListing(
            $user->id,
            $category->id,
            'Перфоратор в Москве',
            'perforator-v-moskve',
            now(),
            [
                'region_id' => $otherRegion->id,
            ],
        );

        $this
            ->getJson('/api/v1/public/listings?regionQuery=краснодарский&cityQuery=краснодар')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $matchedListing->id);

        $this
            ->getJson('/api/v1/public/listings?regionId=' . $region->id . '&cityId=' . $city->id)
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $matchedListing->id);
    }

    public function test_public_listings_validate_filter_values(): void
    {
        $this
            ->getJson('/api/v1/public/listings?minPrice=90000&maxPrice=1000&sort=unknown&isNegotiable=maybe&regionQuery=' . str_repeat('x', 121))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['maxPrice', 'sort', 'isNegotiable', 'regionQuery']);
    }

    public function test_public_listings_query_count_does_not_grow_per_listing(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $rootCategory       = $categoryRepository->save([
            'name'         => 'Промышленное оборудование',
            'slug'         => 'promyshlennoe-oborudovanie',
            'catalog_type' => 1,
        ]);
        $childCategory      = $categoryRepository->save([
            'parent_id'    => $rootCategory->id,
            'name'         => 'Станки',
            'slug'         => 'stanki',
            'catalog_type' => 1,
        ]);
        $leafCategory       = $categoryRepository->save([
            'parent_id'    => $childCategory->id,
            'name'         => 'Токарные станки',
            'slug'         => 'tokarnye-stanki',
            'catalog_type' => 1,
        ]);

        for ($index = 1; $index <= 8; $index++) {
            $this->createPublishedListing(
                $user->id,
                $leafCategory->id,
                'Токарный станок ' . $index,
                'tokarnyj-stanok-' . $index,
                now()->subMinutes($index),
            );
        }

        $queryCount         = $this->countQueries(function (): void {
            $this
                ->getJson('/api/v1/public/listings?perPage=8')
                ->assertOk()
                ->assertJsonCount(8, 'data.items')
                ->assertJsonPath('data.items.0.category.fullName', 'Промышленное оборудование / Станки / Токарные станки');
        });

        $this->assertLessThanOrEqual(
            12,
            $queryCount,
            'Public listings endpoint must not add queries per listing while resolving categories, media and breadcrumbs.',
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPublishedListing(
        string $userId,
        string $categoryId,
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

    private function countQueries(callable $callback): int
    {
        $queryCount = 0;

        DB::listen(static function () use (&$queryCount): void {
            $queryCount++;
        });

        $callback();

        return $queryCount;
    }
}
