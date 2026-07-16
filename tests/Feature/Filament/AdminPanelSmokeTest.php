<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Filament\Resources\Listings\ListingResource;
use App\Listing\Infrastructure\Models\EloquentListing;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Filament\Resources\Media\MediaResource;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class AdminPanelSmokeTest extends FeatureTestCase
{
    public function test_filament_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk();
    }

    public function test_super_admin_can_render_critical_filament_pages(): void
    {
        $admin    = $this->createSuperAdmin();
        $category = EloquentCategory::factory()->create();
        $listing  = EloquentListing::factory()->create([
            'category_id' => $category->id,
            'user_id'     => EloquentUser::factory()->create()->id,
        ]);
        $media    = $this->createMedia($admin);

        $this->actingAs($admin, 'admin');

        $this->get('/admin')
            ->assertOk();

        $this->get(CategoryResource::getUrl('index'))
            ->assertOk();
        $this->get(CategoryResource::getUrl('edit', ['record' => $category]))
            ->assertOk()
            ->assertSee($category->name);

        $this->get(MediaResource::getUrl('index'))
            ->assertOk();
        $this->get(MediaResource::getUrl('edit', ['record' => $media]))
            ->assertOk()
            ->assertSee($media->name);

        $this->get(ListingResource::getUrl('index'))
            ->assertOk();
        $this->get(ListingResource::getUrl('view', ['record' => $listing]))
            ->assertOk()
            ->assertSee($listing->title)
            ->assertSee('Сменить статус');
    }

    private function createSuperAdmin(): EloquentAdmin
    {
        $admin = EloquentAdmin::query()->create([
            'name'     => 'Filament Smoke Admin',
            'email'    => 'filament-smoke@example.com',
            'password' => 'password',
        ]);
        $role  = Role::findOrCreate('super_admin', 'admin');

        $admin->assignRole($role);

        return $admin;
    }

    private function createMedia(EloquentAdmin $admin): EloquentMedia
    {
        return EloquentMedia::query()->create([
            'model_type'            => null,
            'model_id'              => null,
            'uuid'                  => (string) Str::uuid(),
            'collection_name'       => 'images',
            'name'                  => 'Filament smoke image',
            'file_name'             => 'filament-smoke.jpg',
            'mime_type'             => 'image/jpeg',
            'disk'                  => 'public',
            'conversions_disk'      => 'public',
            'size'                  => 1024,
            'manipulations'         => [],
            'custom_properties'     => [],
            'generated_conversions' => [],
            'responsive_images'     => [],
            'media_type'            => MediaType::IMAGE,
            'visibility'            => MediaVisibility::PUBLIC,
            'uploaded_by_admin_id'  => $admin->id,
            'order_column'          => 1,
        ]);
    }
}
