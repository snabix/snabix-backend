<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
        });

        DB::table('users')
            ->where('first_name', 'User')
            ->where('last_name', 'Account')
            ->update([
                'first_name' => null,
                'last_name'  => null,
            ]);
    }

    public function down(): void
    {
        // Removing fabricated personal data is intentionally irreversible.
    }
};
