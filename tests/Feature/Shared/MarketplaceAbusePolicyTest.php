<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\Feature\FeatureTestCase;

class MarketplaceAbusePolicyTest extends FeatureTestCase
{
    public function test_unverified_user_cannot_create_trust_sensitive_marketplace_content(): void
    {
        $user   = EloquentUser::factory()->unverified()->create();
        $seller = EloquentUser::factory()->create();

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [])
            ->assertForbidden()
            ->assertJson([
                'code'                 => 'auth.email-verification-required',
                'verificationRequired' => true,
            ]);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/users/' . $seller->id . '/reviews', [])
            ->assertForbidden()
            ->assertJson([
                'code'                 => 'auth.email-verification-required',
                'verificationRequired' => true,
            ]);

        $logs   = EloquentSystemLog::query()
            ->where('category', 'abuse')
            ->where('user_id', $user->id)
            ->whereIn('action', ['listing.create', 'review.create'])
            ->get();

        $this->assertCount(2, $logs);
        $this->assertEqualsCanonicalizing(
            ['listing.create', 'review.create'],
            $logs->pluck('action')->all(),
        );
        $this->assertTrue($logs->every(
            fn(EloquentSystemLog $log): bool => $log->context === [
                'reason'         => 'email_verification_required',
                'policy_version' => 1,
            ],
        ));
    }

    public function test_listing_create_limit_returns_retry_contract_and_resets_after_window(): void
    {
        config([
            'marketplace-abuse.limits.listing_create.user.attempts'     => 2,
            'marketplace-abuse.limits.listing_create.user.decay_seconds'=> 1,
            'marketplace-abuse.limits.listing_create.ip.attempts'       => 100,
            'marketplace-abuse.limits.listing_create.ip.decay_seconds'  => 1,
        ]);

        $user       = EloquentUser::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/listings', [])->assertUnprocessable();
        $this->actingAs($user)->postJson('/api/v1/listings', [])->assertUnprocessable();

        $blocked    = $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [])
            ->assertTooManyRequests()
            ->assertJsonPath('code', 'abuse.rate-limit-exceeded');

        $retryAfter = (int) $blocked->headers->get('Retry-After');

        $this->assertGreaterThan(0, $retryAfter);
        $this->assertSame($retryAfter, $blocked->json('retryAfterSeconds'));

        $log        = EloquentSystemLog::query()
            ->where('category', 'abuse')
            ->where('action', 'listing.create')
            ->where('status_code', 429)
            ->latest('created_at')
            ->first();

        $this->assertInstanceOf(EloquentSystemLog::class, $log);
        $this->assertSame('rate_limit_exceeded', $log->context['reason'] ?? null);
        $this->assertSame('listing_create', $log->context['scope'] ?? null);
        $this->assertSame('user', $log->context['dimension'] ?? null);

        usleep(1_100_000);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/listings', [])
            ->assertUnprocessable();
    }

    public function test_public_resource_limits_use_independent_non_global_buckets(): void
    {
        config([
            'marketplace-abuse.limits.catalog_read.ip.attempts'       => 1,
            'marketplace-abuse.limits.catalog_read.ip.decay_seconds'  => 60,
            'marketplace-abuse.limits.location_read.ip.attempts'      => 1,
            'marketplace-abuse.limits.location_read.ip.decay_seconds' => 60,
        ]);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.10']);

        $this->getJson('/api/v1/categories/list')->assertOk();

        $this
            ->getJson('/api/v1/categories/list')
            ->assertTooManyRequests()
            ->assertJsonPath('code', 'abuse.rate-limit-exceeded');

        $this->getJson('/api/v1/locations/regions')->assertOk();
    }

    public function test_marketplace_routes_declare_specialized_abuse_policy(): void
    {
        $expectations = [
            'GET api/v1/categories/list'                            => ['throttle:marketplace.catalog-read'],
            'GET api/v1/categories/{categoryId}/branch'             => ['throttle:marketplace.catalog-read'],
            'GET api/v1/categories/{categoryId}/attributes'         => ['throttle:marketplace.catalog-read'],
            'GET api/v1/locations/regions'                          => ['throttle:marketplace.location-read'],
            'GET api/v1/locations/cities'                           => ['throttle:marketplace.location-read'],
            'GET api/v1/news'                                       => ['throttle:marketplace.news-read'],
            'GET api/v1/news/{slug}'                                => ['throttle:marketplace.news-read'],
            'GET api/v1/public/listings'                            => ['throttle:marketplace.listing-read'],
            'GET api/v1/public/listings/{listingId}'                => ['throttle:marketplace.listing-read'],
            'GET api/v1/users/{userId}/reviews'                     => ['throttle:marketplace.review-read'],
            'POST api/v1/users/{userId}/reviews'                    => [
                'marketplace.verified:review.create',
                'throttle:marketplace.review-create',
            ],
            'GET api/v1/listings'                                   => ['throttle:marketplace.account-listing-read'],
            'GET api/v1/listings/favorites'                         => ['throttle:marketplace.account-listing-read'],
            'POST api/v1/listings'                                  => [
                'marketplace.verified:listing.create',
                'throttle:marketplace.listing-create',
            ],
            'POST api/v1/listings/{listingId}/archive'              => ['throttle:marketplace.listing-write'],
            'POST api/v1/listings/{listingId}/submit-for-review'    => [
                'marketplace.verified:listing.submit-for-review',
                'throttle:marketplace.listing-submit',
            ],
            'POST api/v1/listings/{listingId}/media'                => [
                'marketplace.verified:listing.media.upload',
                'throttle:marketplace.listing-media-write',
            ],
            'PATCH api/v1/listings/{listingId}/media/reorder'       => ['throttle:marketplace.listing-media-write'],
            'PATCH api/v1/listings/{listingId}/media/{mediaId}/main'=> ['throttle:marketplace.listing-media-write'],
            'DELETE api/v1/listings/{listingId}/media/{mediaId}'    => ['throttle:marketplace.listing-media-write'],
            'POST api/v1/listings/{listingId}/favorite'             => ['throttle:marketplace.favorite-write'],
            'DELETE api/v1/listings/{listingId}/favorite'           => ['throttle:marketplace.favorite-write'],
            'GET api/v1/listings/{listingId}'                       => ['throttle:marketplace.account-listing-read'],
            'PATCH api/v1/listings/{listingId}'                     => ['throttle:marketplace.listing-write'],
            'DELETE api/v1/listings/{listingId}'                    => ['throttle:marketplace.listing-write'],
        ];

        foreach ($expectations as $routeSignature => $requiredMiddleware) {
            [$method, $uri] = explode(' ', $routeSignature, 2);
            $middleware     = $this->route($method, $uri)->gatherMiddleware();

            foreach ($requiredMiddleware as $required) {
                $this->assertContains($required, $middleware, $routeSignature);
            }
        }
    }

    private function route(string $method, string $uri): Route
    {
        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            if ($route->uri() === $uri && in_array($method, $route->methods(), true)) {
                return $route;
            }
        }

        $this->fail(sprintf('Route [%s %s] is not registered.', $method, $uri));
    }
}
