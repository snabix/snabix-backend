<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Application\Support\PublicListingPayloadMapper;
use App\Listing\Domain\Contracts\PublicListingQueryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Facades\DB;
use Tests\Feature\FeatureTestCase;

class ListingBreadcrumbQueryTest extends FeatureTestCase
{
    public function test_public_listing_breadcrumbs_use_one_cached_category_query(): void
    {
        $root                       = EloquentCategory::factory()->create([
            'name'  => 'Электроника',
            'slug'  => 'electronics',
            'path'  => 'electronics',
            'depth' => 0,
        ]);
        $child                      = EloquentCategory::factory()->childOf($root)->create([
            'name' => 'Компьютеры',
            'slug' => 'computers',
        ]);
        $leaf                       = EloquentCategory::factory()->childOf($child)->create([
            'name' => 'Ноутбуки',
            'slug' => 'laptops',
        ]);
        $user                       = EloquentUser::factory()->create();

        EloquentListing::factory()
            ->count(5)
            ->published()
            ->create([
                'user_id'     => $user->id,
                'category_id' => $leaf->id,
            ]);

        $query                      = app(PublicListingQueryInterface::class);
        $mapper                     = app(PublicListingPayloadMapper::class);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $paginator                  = $query->listPublicPublished(perPage: 10);
        $queriesBeforeMapping       = count(DB::getQueryLog());
        $payloads                   = $paginator->getCollection()
            ->map(fn(EloquentListing $listing): array => $mapper->map($listing))
            ->values();
        $mappingQueryCount          = count(DB::getQueryLog()) - $queriesBeforeMapping;
        $firstPayload               = $payloads->first();

        $this->assertSame(1, $mappingQueryCount);
        $this->assertCount(5, $payloads);
        $this->assertIsArray($firstPayload);

        $categoryPayload            = $firstPayload['category'] ?? null;

        $this->assertIsArray($categoryPayload);
        $this->assertSame('Электроника / Компьютеры / Ноутбуки', $categoryPayload['fullName'] ?? null);
        $this->assertSame(
            [
                ['id' => $root->id, 'name' => 'Электроника', 'slug' => 'electronics'],
                ['id' => $child->id, 'name' => 'Компьютеры', 'slug' => 'computers'],
                ['id' => $leaf->id, 'name' => 'Ноутбуки', 'slug' => 'laptops'],
            ],
            $categoryPayload['breadcrumbs'] ?? null,
        );

        $queriesBeforeCachedMapping = count(DB::getQueryLog());
        $paginator->getCollection()->each(
            fn(EloquentListing $listing): array => $mapper->map($listing),
        );

        $this->assertSame($queriesBeforeCachedMapping, count(DB::getQueryLog()));

        DB::disableQueryLog();
    }
}
