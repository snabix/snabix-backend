<?php

declare(strict_types=1);

namespace Tests\Feature\Review;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Str;

trait CreatesPublishedListingForReview
{
    private function createPublishedListing(string $sellerId): EloquentListing
    {
        $suffix   = (string) Str::uuid();
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Отзывы',
            'slug'         => 'otzyvy-' . $suffix,
            'catalog_type' => 1,
        ]);

        return EloquentListing::query()->create([
            'user_id'       => $sellerId,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => 'Тестовое объявление',
            'slug'          => 'testovoe-obyavlenie-' . $suffix,
            'description'   => 'Описание объявления для отзыва.',
            'price'         => 10000,
            'currency'      => 'RUB',
            'is_negotiable' => false,
            'published_at'  => now(),
        ]);
    }
}
