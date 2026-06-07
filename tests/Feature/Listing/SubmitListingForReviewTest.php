<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Contracts\CategoryAttributeDefinitionRepositoryInterface;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Tests\Feature\FeatureTestCase;

class SubmitListingForReviewTest extends FeatureTestCase
{
    public function test_user_can_submit_draft_listing_for_review(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $category                      = $categoryRepository->save([
            'name'         => 'Телефоны',
            'slug'         => 'telefony-submit',
            'catalog_type' => 1,
        ]);
        $brandAttribute                = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Бренд',
            'slug'                => 'brend-submit',
            'type'                => 1,
            'is_required'         => true,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);
        $listing                       = $this->createListing($user->id, $category->id, ListingStatus::DRAFT);

        EloquentListingAttributeValue::query()->create([
            'listing_id'              => $listing->id,
            'attribute_definition_id' => $brandAttribute->id,
            'value'                   => ['Samsung'],
            'display_value'           => 'Samsung',
        ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings/' . $listing->id . '/submit-for-review')
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id)
            ->assertJsonPath('data.status', ListingStatus::PENDING_REVIEW->value);

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => ListingStatus::PENDING_REVIEW->value,
        ]);

        $this->assertDatabaseHas('system_logs', [
            'category' => 'listing',
            'action'   => 'listing.submit-for-review',
            'user_id'  => $user->id,
        ]);
    }

    public function test_user_cannot_submit_draft_listing_without_required_attributes(): void
    {
        $categoryRepository            = app(CategoryRepositoryInterface::class);
        $attributeDefinitionRepository = app(CategoryAttributeDefinitionRepositoryInterface::class);
        $user                          = EloquentUser::factory()->create();
        $category                      = $categoryRepository->save([
            'name'         => 'Ноутбуки submit',
            'slug'         => 'noutbuki-submit',
            'catalog_type' => 1,
        ]);
        $attribute                     = $attributeDefinitionRepository->save([
            'category_id'         => $category->id,
            'name'                => 'Производитель',
            'slug'                => 'proizvoditel-submit',
            'type'                => 1,
            'is_required'         => true,
            'is_filterable'       => true,
            'is_active'           => true,
            'applies_to_children' => true,
            'sort_order'          => 10,
        ]);
        $listing                       = $this->createListing($user->id, $category->id, ListingStatus::DRAFT);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings/' . $listing->id . '/submit-for-review')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attributeValues.' . $attribute->id]);

        $this->assertDatabaseHas('listings', [
            'id'     => $listing->id,
            'status' => ListingStatus::DRAFT->value,
        ]);
    }

    public function test_user_cannot_submit_listing_from_invalid_status(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Мониторы submit',
            'slug'         => 'monitory-submit',
            'catalog_type' => 1,
        ]);
        $listing            = $this->createListing($user->id, $category->id, ListingStatus::PUBLISHED);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings/' . $listing->id . '/submit-for-review')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_cannot_submit_another_users_listing_for_review(): void
    {
        $categoryRepository = app(CategoryRepositoryInterface::class);
        $user               = EloquentUser::factory()->create();
        $otherUser          = EloquentUser::factory()->create();
        $category           = $categoryRepository->save([
            'name'         => 'Планшеты submit',
            'slug'         => 'planshety-submit',
            'catalog_type' => 1,
        ]);
        $listing            = $this->createListing($otherUser->id, $category->id, ListingStatus::DRAFT);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings/' . $listing->id . '/submit-for-review')
            ->assertNotFound();
    }

    private function createListing(
        string $userId,
        string $categoryId,
        ListingStatus $status,
    ): EloquentListing {
        return EloquentListing::query()->create([
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'type'          => ListingType::PRODUCT,
            'status'        => $status,
            'condition'     => ListingCondition::USED,
            'title'         => 'Тестовое объявление',
            'slug'          => 'testovoe-obyavlenie-' . mb_strtolower($status->name),
            'description'   => 'Описание тестового объявления.',
            'price'         => 15000,
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);
    }
}
