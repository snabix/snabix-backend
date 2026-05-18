<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Infrastructure\Policies;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Policies\ListingPolicy;
use PHPUnit\Framework\TestCase;

class ListingPolicyTest extends TestCase
{
    public function test_owner_can_manage_listing(): void
    {
        $user    = $this->makeUser('user-1');
        $listing = $this->makeListing('user-1');
        $policy  = new ListingPolicy();

        $this->assertTrue($policy->view($user, $listing)->allowed());
        $this->assertTrue($policy->update($user, $listing)->allowed());
        $this->assertTrue($policy->delete($user, $listing)->allowed());
        $this->assertTrue($policy->submitForReview($user, $listing)->allowed());
    }

    public function test_non_owner_receives_not_found_denial(): void
    {
        $user     = $this->makeUser('user-1');
        $listing  = $this->makeListing('user-2');
        $policy   = new ListingPolicy();

        $response = $policy->view($user, $listing);

        $this->assertFalse($response->allowed());
        $this->assertSame(404, $response->status());
    }

    private function makeUser(string $id): EloquentUser
    {
        $user     = new EloquentUser();
        $user->id = $id;

        return $user;
    }

    private function makeListing(string $userId): EloquentListing
    {
        $listing          = new EloquentListing([
            'user_id'       => $userId,
            'category_id'   => 1,
            'type'          => ListingType::PRODUCT,
            'status'        => ListingStatus::DRAFT,
            'condition'     => ListingCondition::USED,
            'title'         => 'Тестовое объявление',
            'slug'          => 'testovoe-obyavlenie',
            'description'   => 'Описание тестового объявления.',
            'currency'      => 'RUB',
            'is_negotiable' => true,
        ]);
        $listing->user_id = $userId;

        return $listing;
    }
}
