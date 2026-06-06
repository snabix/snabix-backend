<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        EloquentUser::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'id'                => (string) Str::uuid(),
                'first_name'        => 'Test',
                'last_name'         => 'User',
                'phone_number'      => '+79990000000',
                'is_active'         => true,
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ],
        );

        $this->call([
            CatalogDemoSeeder::class,
            ListingsDemoSeeder::class,
            NewsDemoSeeder::class,
        ]);
    }
}
