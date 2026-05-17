<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentListing>
 */
class EloquentListingFactory extends Factory
{
    protected $model = EloquentListing::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $category = EloquentCategory::query()->inRandomOrder()->first() ?? EloquentCategory::factory()->create();
        $user     = EloquentUser::query()->inRandomOrder()->first() ?? EloquentUser::factory()->create();
        $title    = fake()->unique()->sentence(fake()->numberBetween(3, 6));
        $slug     = Str::slug($title) ?: 'listing-' . fake()->unique()->numberBetween(1, 999999);
        $type     = $category->catalog_type === CategoryCatalogType::SERVICE
            ? ListingType::SERVICE
            : ListingType::PRODUCT;
        $status   = fake()->randomElement([
            ListingStatus::PUBLISHED,
            ListingStatus::PUBLISHED,
            ListingStatus::DRAFT,
            ListingStatus::PENDING_REVIEW,
        ]);

        return [
            'user_id'          => $user->id,
            'category_id'      => $category->id,
            'type'             => $type,
            'status'           => $status,
            'condition'        => $type === ListingType::SERVICE
                ? ListingCondition::NOT_APPLICABLE
                : fake()->randomElement([ListingCondition::NEW, ListingCondition::USED]),
            'title'            => $title,
            'slug'             => $slug,
            'description'      => fake()->paragraphs(asText: true),
            'price'            => $type === ListingType::SERVICE
                ? fake()->numberBetween(500, 150000)
                : fake()->numberBetween(1000, 500000),
            'currency'         => 'RUB',
            'is_negotiable'    => fake()->boolean(35),
            'contact_name'     => fake()->name(),
            'contact_phone'    => '+79' . fake()->numerify('#########'),
            'contact_email'    => fake()->safeEmail(),
            'views_count'      => fake()->numberBetween(0, 2500),
            'is_featured'      => fake()->boolean(12),
            'rejection_reason' => $status === ListingStatus::REJECTED ? fake()->sentence() : null,
            'published_at'     => $status === ListingStatus::PUBLISHED ? now()->subDays(fake()->numberBetween(0, 30)) : null,
            'expires_at'       => $status === ListingStatus::PUBLISHED ? now()->addDays(fake()->numberBetween(7, 90)) : null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn(): array => [
            'status'       => ListingStatus::PUBLISHED,
            'published_at' => now()->subDays(fake()->numberBetween(0, 15)),
            'expires_at'   => now()->addDays(fake()->numberBetween(7, 60)),
        ]);
    }
}
