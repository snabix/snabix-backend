<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Providers;

use App\Shared\Infrastructure\RateLimiting\MarketplaceRateLimitPolicy;
use App\Shared\Infrastructure\Services\AbuseEventLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AbuseProtectionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AbuseEventLogger::class);
        $this->app->singleton(MarketplaceRateLimitPolicy::class);
    }

    public function boot(MarketplaceRateLimitPolicy $policy): void
    {
        foreach ($this->publicLimiters() as $name => [$scope, $action]) {
            RateLimiter::for(
                $name,
                fn(Request $request): array => $policy->publicLimits($request, $scope, $action),
            );
        }

        foreach ($this->authenticatedLimiters() as $name => [$scope, $action]) {
            RateLimiter::for(
                $name,
                fn(Request $request): array => $policy->authenticatedLimits($request, $scope, $action),
            );
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    private function publicLimiters(): array
    {
        return [
            'marketplace.catalog-read'  => ['catalog_read', 'catalog.read'],
            'marketplace.location-read' => ['location_read', 'location.read'],
            'marketplace.listing-read'  => ['listing_read', 'listing.public.read'],
            'marketplace.news-read'     => ['news_read', 'news.read'],
            'marketplace.review-read'   => ['review_read', 'review.public.read'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    private function authenticatedLimiters(): array
    {
        return [
            'marketplace.account-listing-read' => ['account_listing_read', 'listing.account.read'],
            'marketplace.listing-create'       => ['listing_create', 'listing.create'],
            'marketplace.listing-write'        => ['listing_write', 'listing.write'],
            'marketplace.listing-submit'       => ['listing_submit', 'listing.submit-for-review'],
            'marketplace.listing-media-write'  => ['listing_media_write', 'listing.media.write'],
            'marketplace.favorite-write'       => ['favorite_write', 'listing.favorite.write'],
            'marketplace.review-create'        => ['review_create', 'review.create'],
        ];
    }
}
