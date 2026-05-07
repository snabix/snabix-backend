<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        EloquentAdmin::query()->firstOrCreate(
            ['email' => 'admin@test.local'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('admin12345'),
                'email_verified_at' => now(),
            ],
        );
    }
}
