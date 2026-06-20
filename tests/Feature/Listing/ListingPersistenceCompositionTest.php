<?php

declare(strict_types=1);

namespace Tests\Feature\Listing;

use App\Listing\Domain\Contracts\ListingReadRepositoryInterface;
use App\Listing\Domain\Contracts\ListingWriterInterface;
use App\Listing\Domain\Contracts\OwnedListingQueryInterface;
use App\Listing\Domain\Contracts\PublicListingQueryInterface;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Listing\Infrastructure\Queries\OwnedListingQuery;
use App\Listing\Infrastructure\Queries\PublicListingQuery;
use App\Listing\Infrastructure\Repositories\EloquentListingReadRepository;
use App\Listing\Infrastructure\Repositories\EloquentListingWriter;
use App\Listing\Infrastructure\Services\ListingSlugGenerator;
use Tests\Feature\FeatureTestCase;

class ListingPersistenceCompositionTest extends FeatureTestCase
{
    public function test_listing_persistence_contracts_resolve_to_split_components(): void
    {
        $this->assertInstanceOf(
            EloquentListingReadRepository::class,
            app(ListingReadRepositoryInterface::class),
        );
        $this->assertInstanceOf(
            EloquentListingWriter::class,
            app(ListingWriterInterface::class),
        );
        $this->assertInstanceOf(
            OwnedListingQuery::class,
            app(OwnedListingQueryInterface::class),
        );
        $this->assertInstanceOf(
            PublicListingQuery::class,
            app(PublicListingQueryInterface::class),
        );
    }

    public function test_slug_generator_preserves_unique_create_and_update_behavior(): void
    {
        $listing   = EloquentListing::factory()->create([
            'title' => 'Duplicate listing',
            'slug'  => 'duplicate-listing',
        ]);
        $generator = app(ListingSlugGenerator::class);

        $this->assertSame('duplicate-listing-2', $generator->generate('Duplicate listing'));
        $this->assertSame('duplicate-listing', $generator->generate('Duplicate listing', $listing->id));
    }
}
