<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\FeatureTestCase;

class ListingMediaUploadTest extends FeatureTestCase
{
    public function test_owner_can_upload_listing_images(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user    = EloquentUser::factory()->create();
        $listing = $this->createListingForUser($user);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/media', [
                'images' => [
                    $this->fakePng('first.png'),
                    $this->fakePng('second.png'),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data.imageUrls')
            ->assertJsonPath('data.imageUrl', fn(mixed $url): bool => is_string($url) && $url !== '');

        $this->assertDatabaseCount('media', 2);
        $this->assertDatabaseHas('media', [
            'model_type'      => EloquentListing::class,
            'model_id'        => $listing->id,
            'collection_name' => 'listing-images',
            'media_type'      => MediaType::IMAGE->value,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.media.upload',
            'user_id'  => $user->id,
        ]);

        $media   = EloquentMedia::query()
            ->where('model_type', EloquentListing::class)
            ->where('model_id', $listing->id)
            ->firstOrFail();

        $this->assertEqualsCanonicalizing([
            EloquentListing::MEDIA_CONVERSION_CARD,
            EloquentListing::MEDIA_CONVERSION_GALLERY,
        ], $media->getMediaConversionNames());
    }

    public function test_user_cannot_upload_images_to_another_users_listing(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $owner   = EloquentUser::factory()->create();
        $guest   = EloquentUser::factory()->create();
        $listing = $this->createListingForUser($owner);

        $this
            ->actingAs($guest)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/media', [
                'images' => [
                    $this->fakePng('first.png'),
                ],
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('media', 0);
    }

    public function test_listing_images_are_limited_to_eight_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user    = EloquentUser::factory()->create();
        $listing = $this->createListingForUser($user);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/media', [
                'images' => collect(range(1, 9))
                    ->map(fn(int $index): UploadedFile => $this->fakePng('image-' . $index . '.png'))
                    ->all(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['images']);
    }

    public function test_owner_can_reorder_set_main_and_delete_listing_images(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user     = EloquentUser::factory()->create();
        $listing  = $this->createListingForUser($user);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/listings/' . $listing->id . '/media', [
                'images' => [
                    $this->fakePng('first.png'),
                    $this->fakePng('second.png'),
                    $this->fakePng('third.png'),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(3, 'data.media')
            ->assertJsonPath('data.media.0.isMain', true);

        $mediaIds = EloquentMedia::query()
            ->where('model_type', EloquentListing::class)
            ->where('model_id', $listing->id)
            ->orderBy('order_column')
            ->pluck('id')
            ->all();
        $mediaIds = $this->normalizeIntegerIds($mediaIds);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->patch('/api/v1/listings/' . $listing->id . '/media/reorder', [
                'mediaIds' => [$mediaIds[2], $mediaIds[0], $mediaIds[1]],
            ])
            ->assertOk()
            ->assertJsonPath('data.media.0.id', $mediaIds[2]);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.media.reorder',
            'user_id'  => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->patch('/api/v1/listings/' . $listing->id . '/media/' . $mediaIds[1] . '/main')
            ->assertOk()
            ->assertJsonPath('data.media.0.id', $mediaIds[1])
            ->assertJsonPath('data.media.0.isMain', true);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.media.set-main',
            'user_id'  => $user->id,
        ]);

        $this
            ->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->delete('/api/v1/listings/' . $listing->id . '/media/' . $mediaIds[1])
            ->assertOk()
            ->assertJsonCount(2, 'data.media');
        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.media.delete',
            'user_id'  => $user->id,
        ]);

        $this->assertDatabaseMissing('media', [
            'id' => $mediaIds[1],
        ]);
    }

    private function createListingForUser(EloquentUser $user): EloquentListing
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $category           = $categoryRepository->save([
            'name'         => 'Фототовары',
            'slug'         => 'fototovary',
            'catalog_type' => 1,
        ]);

        return EloquentListing::query()->create([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Фотоаппарат',
            'slug'          => 'fotoapparat',
            'description'   => 'Фотоаппарат в хорошем состоянии.',
            'price'         => 45000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true) ?: '',
        );
    }

    /**
     * @param  array<int, mixed> $ids
     * @return list<int>
     */
    private function normalizeIntegerIds(array $ids): array
    {
        $normalizedIds = [];

        foreach ($ids as $id) {
            if (is_int($id)) {
                $normalizedIds[] = $id;

                continue;
            }

            if (is_string($id) && ctype_digit($id)) {
                $normalizedIds[] = (int) $id;
            }
        }

        return $normalizedIds;
    }
}
