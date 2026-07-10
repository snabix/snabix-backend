<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Seeder;

class ListingsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentListing::query()->count() >= 20) {
            return;
        }

        if (EloquentUser::query()->count() < 5) {
            EloquentUser::factory()->count(5)->create();
        }

        if (! EloquentCategory::query()->exists()) {
            $this->command->warn('Demo-объявления пропущены: сначала импортируйте категории.');

            return;
        }

        EloquentListing::factory()
            ->count(20)
            ->create();
    }
}
