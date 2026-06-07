<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')
                ->nullable();
            $table->string('last_name')
                ->nullable();
            $table->string('phone_number')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                $name      = trim((string) ($user->name ?? ''));
                $parts     = preg_split('/\s+/u', $name, 2) ?: [];
                $firstName = $parts[0] ?? 'User';
                $lastName  = $parts[1] ?? 'Account';

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'first_name' => $firstName,
                        'last_name'  => $lastName,
                    ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')
                ->nullable();
        });

        DB::table('users')
            ->select(['id', 'first_name', 'last_name'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'name' => trim(implode(' ', array_filter([
                            $user->first_name,
                            $user->last_name,
                        ]))),
                    ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone_number',
                'is_active',
            ]);

        });
    }
};
