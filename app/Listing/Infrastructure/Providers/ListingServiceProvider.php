<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Providers;

use App\Listing\Application\Support\ListingPayloadMapper;
use App\Listing\Application\Support\PaginationPayloadMapper;
use App\Listing\Application\Support\PublicListingPayloadMapper;
use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Contracts\OwnedListingQueryInterface;
use App\Listing\Domain\Contracts\PublicListingQueryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Policies\ListingPolicy;
use App\Listing\Infrastructure\Queries\OwnedListingQuery;
use App\Listing\Infrastructure\Queries\PublicListingQuery;
use App\Listing\Infrastructure\Repositories\EloquentListingReadRepository;
use App\Listing\Infrastructure\Repositories\EloquentListingWriter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ListingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ListingReadRepositoryInterface::class,
            EloquentListingReadRepository::class,
        );
        $this->app->bind(
            ListingWriterInterface::class,
            EloquentListingWriter::class,
        );
        $this->app->bind(
            OwnedListingQueryInterface::class,
            OwnedListingQuery::class,
        );
        $this->app->bind(
            PublicListingQueryInterface::class,
            PublicListingQuery::class,
        );
        $this->app->scoped(ListingPayloadMapper::class);
        $this->app->scoped(PublicListingPayloadMapper::class);
        $this->app->singleton(PaginationPayloadMapper::class);
    }

    public function boot(): void
    {
        Gate::policy(EloquentListing::class, ListingPolicy::class);
    }
}
