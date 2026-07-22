<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingFavorite;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature\FeatureTestCase;

class ListingFavoriteTest extends FeatureTestCase
{
    /** @var list<string> */
    private const array PRIVATE_LISTING_FIELDS = [
        'userId',
        'contactName',
        'contactPhone',
        'contactEmail',
        'rejectionReason',
        'media',
    ];

    public function test_user_can_add_list_and_remove_favorite_listing(): void
    {
        $user           = EloquentUser::factory()->create();
        $seller         = EloquentUser::factory()->create();
        $category       = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Ноутбуки',
            'slug'         => 'noutbuki',
            'catalog_type' => 1,
        ]);
        $listing        = $this->createPublishedListing($seller->id, $category->id, [
            'contact_name'     => 'Private Seller',
            'contact_phone'    => '+79990000001',
            'contact_email'    => 'private@example.test',
            'rejection_reason' => 'Private moderation note',
        ]);

        $addResponse    = $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id)
            ->assertJsonPath('data.isFavorite', true);

        $this->assertPublicListingPayload($addResponse, 'data');

        $this->assertDatabaseHas('listing_favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);

        $listResponse   = $this
            ->actingAs($user)
            ->getJson('/api/v1/listings/favorites')
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', $listing->id)
            ->assertJsonPath('data.items.0.isFavorite', true);

        $this->assertPublicListingPayload($listResponse, 'data.items.0');

        $removeResponse = $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->delete('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertOk()
            ->assertJsonPath('data.isFavorite', false);

        $this->assertPublicListingPayload($removeResponse, 'data');

        $this->assertDatabaseMissing('listing_favorites', [
            'user_id'    => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_draft_listing_cannot_be_added_to_favorites(): void
    {
        $user     = EloquentUser::factory()->create();
        $seller   = EloquentUser::factory()->create();
        $category = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Черновики',
            'slug'         => 'chernoviki',
            'catalog_type' => 1,
        ]);
        $listing  = $this->createPublishedListing($seller->id, $category->id, [
            'status' => ListingStatus::DRAFT,
        ]);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/favorite')
            ->assertNotFound();
    }

    public function test_favorite_listing_query_count_does_not_grow_per_seller(): void
    {
        $user       = EloquentUser::factory()->create();
        $category   = app(CategoryRepositoryInterface::class)->save([
            'name'         => 'Промышленное оборудование',
            'slug'         => 'promyshlennoe-oborudovanie',
            'catalog_type' => 1,
        ]);

        for ($index = 1; $index <= 8; $index++) {
            $seller  = EloquentUser::factory()->create();
            $listing = $this->createPublishedListing($seller->id, $category->id, [
                'title' => 'Избранное объявление ' . $index,
                'slug'  => 'izbrannoe-obyavlenie-' . $index,
            ]);

            EloquentListingFavorite::query()->create([
                'user_id'    => $user->id,
                'listing_id' => $listing->id,
            ]);
        }

        $this->actingAs($user);

        $queryCount = $this->countQueries(function (): void {
            $this
                ->getJson('/api/v1/listings/favorites?perPage=8')
                ->assertOk()
                ->assertJsonCount(8, 'data.items');
        });

        $this->assertLessThanOrEqual(
            12,
            $queryCount,
            'Favorites endpoint must eager-load seller data instead of querying each listing owner.',
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createPublishedListing(string $userId, string $categoryId, array $overrides = []): EloquentListing
    {
        return EloquentListing::query()->create([
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::PUBLISHED,
            'condition'     => ListingCondition::USED,
            'title'         => 'Игровой ноутбук',
            'slug'          => 'igrovoj-noutbuk',
            'description'   => 'Ноутбук в хорошем состоянии.',
            'price'         => 85000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
            'published_at'  => now(),
            ...$overrides,
        ]);
    }

    /**
     * @param TestResponse<\Symfony\Component\HttpFoundation\Response> $response
     */
    private function assertPublicListingPayload(TestResponse $response, string $path): void
    {
        foreach (self::PRIVATE_LISTING_FIELDS as $field) {
            $response->assertJsonMissingPath($path . '.' . $field);
        }
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
