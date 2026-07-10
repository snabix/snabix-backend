<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\FeatureTestCase;

class BootstrapDemoDataCommandTest extends FeatureTestCase
{
    public function test_demo_bootstrap_creates_admin_and_listings_after_existing_categories(): void
    {
        $category = EloquentCategory::factory()->create([
            'slug' => 'imported-category',
            'name' => 'Imported Category',
        ]);

        $exitCode = Artisan::call('app:bootstrap-demo-data', [
            '--admin-name'           => 'Bootstrap Admin',
            '--admin-email'          => 'bootstrap-admin@snabix.test',
            '--admin-password'       => 'secret-password',
            '--skip-location-import' => true,
            '--skip-category-import' => true,
            '--skip-news'            => true,
        ]);

        $this->assertSame(0, $exitCode);

        $admin    = EloquentAdmin::query()
            ->where('email', 'bootstrap-admin@snabix.test')
            ->first();

        $this->assertInstanceOf(EloquentAdmin::class, $admin);
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertDatabaseHas('categories', [
            'id'   => $category->id,
            'slug' => 'imported-category',
        ]);
        $this->assertGreaterThan(0, EloquentListing::query()->where('category_id', $category->id)->count());
    }
}
