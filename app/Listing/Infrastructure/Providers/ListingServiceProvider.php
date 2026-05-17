<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Providers;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingRepositoryInterface;
use App\Listing\Infrastructure\Repositories\EloquentListingRepository;
use Illuminate\Support\ServiceProvider;

class ListingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ListingRepositoryInterface::class,
            EloquentListingRepository::class,
        );
        $this->app->singleton(ListingPayloadMapper::class);
    }
}
