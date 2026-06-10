<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Catalog\Application\Services\CategoryIconMediaService;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\FeatureTestCase;

class CategoryIconMediaTest extends FeatureTestCase
{
    public function test_category_icon_can_be_uploaded_replaced_and_returned_from_api(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $category   = app(CategoryRepositoryInterface::class)->save([
            'name' => 'Техника и электроника',
            'slug' => 'tekhnika-i-elektronika',
        ]);

        Storage::disk('local')->put('filament-category-icons-temp/electronics.png', 'first-icon');

        /** @var CategoryIconMediaService $service */
        $service    = app(CategoryIconMediaService::class);
        $firstIcon  = $service->replaceFromStoredUpload($category, 'local', 'filament-category-icons-temp/electronics.png');

        $category->refresh();

        $this->assertTrue(($category->getFirstMedia('category_icons'))?->is($firstIcon) === true);
        $this->assertDatabaseHas('media', [
            'id'              => $firstIcon->id,
            'model_type'      => $category::class,
            'model_id'        => $category->id,
            'collection_name' => 'category_icons',
        ]);
        Storage::disk('public')->assertExists($this->expectedMediaPath($firstIcon, 'electronics.png'));

        $this
            ->getJson('/api/v1/categories/list')
            ->assertOk()
            ->assertJsonPath('data.0.icon', $firstIcon->getFullUrl());

        Storage::disk('local')->put('filament-category-icons-temp/electronics-new.png', 'second-icon');

        $secondIcon = $service->replaceFromStoredUpload($category, 'local', 'filament-category-icons-temp/electronics-new.png');

        $category->refresh();

        $this->assertTrue(($category->getFirstMedia('category_icons'))?->is($secondIcon) === true);
        $this->assertDatabaseMissing('media', ['id' => $firstIcon->id]);
        Storage::disk('public')->assertMissing($this->expectedMediaPath($firstIcon, 'electronics.png'));
        Storage::disk('public')->assertExists($this->expectedMediaPath($secondIcon, 'electronics-new.png'));
    }

    private function expectedMediaPath(EloquentMedia $media, string $fileName): string
    {
        return MediaType::IMAGE->directory() . '/category-icons/' . $media->uuid . '/' . $fileName;
    }
}
