<?php

namespace Database\Seeders;

use App\Shared\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // EloquentUser::factory(10)->create();

        EloquentUser::factory()->create([
            'name' => 'Test EloquentUser',
            'email' => 'test@example.com',
        ]);
    }
}
