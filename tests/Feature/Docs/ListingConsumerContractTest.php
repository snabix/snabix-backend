<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Tests\Feature\FeatureTestCase;

class ListingConsumerContractTest extends FeatureTestCase
{
    public function test_public_and_private_listing_responses_keep_required_consumer_fields(): void
    {
        [$user, $listing] = $this->createListing();
        $contract         = $this->contract();

        $public           = $this->getJson('/api/v1/public/listings/' . $listing->id)
            ->assertOk()
            ->json('data');
        $private          = $this->actingAs($user)
            ->getJson('/api/v1/listings/' . $listing->id)
            ->assertOk()
            ->json('data');

        $this->assertIsArray($public);
        $this->assertIsArray($private);
        $this->assertRequiredFields($public, $contract['publicListing']['requiredFields']);
        $this->assertRequiredFields($private, $contract['privateListing']['requiredFields']);
    }

    public function test_public_listing_response_enforces_snapshot_privacy_boundary(): void
    {
        [, $listing] = $this->createListing();
        $contract    = $this->contract();
        $public      = $this->getJson('/api/v1/public/listings/' . $listing->id)
            ->assertOk()
            ->json('data');

        $this->assertIsArray($public);

        foreach ($contract['publicListing']['forbiddenFields'] as $field) {
            $this->assertArrayNotHasKey($field, $public, sprintf(
                'Public listing response leaked private field %s.',
                $field,
            ));
        }
    }

    /**
     * @return array{EloquentUser, EloquentListing}
     */
    private function createListing(): array
    {
        $user     = EloquentUser::factory()->create();
        $category = EloquentCategory::factory()->create();
        $listing  = EloquentListing::factory()->create([
            'user_id'          => $user->id,
            'category_id'      => $category->id,
            'type'             => ListingType::PRODUCT,
            'status'           => ListingStatus::PUBLISHED,
            'condition'        => ListingCondition::USED,
            'contact_name'     => 'Contract Seller',
            'contact_phone'    => '+79990000000',
            'contact_email'    => 'seller@example.test',
            'rejection_reason' => null,
            'published_at'     => now(),
        ]);

        return [$user, $listing];
    }

    /**
     * @return array{
     *   publicListing: array{requiredFields: list<string>, forbiddenFields: list<string>},
     *   privateListing: array{requiredFields: list<string>}
     * }
     */
    private function contract(): array
    {
        $contents = file_get_contents(base_path('contracts/listings.v1.json'));

        $this->assertIsString($contents);

        /** @var array{
         *   publicListing: array{requiredFields: list<string>, forbiddenFields: list<string>},
         *   privateListing: array{requiredFields: list<string>}
         * } $contract
         */
        $contract = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return $contract;
    }

    /**
     * @param array<string, mixed> $payload
     * @param list<string>         $requiredFields
     */
    private function assertRequiredFields(array $payload, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $payload, sprintf(
                'Listing response misses required consumer field %s.',
                $field,
            ));
        }
    }
}
