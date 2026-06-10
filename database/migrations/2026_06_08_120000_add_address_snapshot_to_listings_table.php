<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->foreignUuid('profile_address_id')
                ->nullable()
                ->after('contact_email')
                ->constrained('user_addresses')
                ->nullOnDelete();
            $table->foreignId('region_id')
                ->nullable()
                ->after('profile_address_id')
                ->constrained('regions')
                ->nullOnDelete();
            $table->foreignId('city_id')
                ->nullable()
                ->after('region_id')
                ->constrained('cities')
                ->nullOnDelete();
            $table->json('address_snapshot')
                ->nullable()
                ->after('city_id');

            $table->index(['region_id', 'status']);
            $table->index(['city_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table): void {
            $table->dropIndex(['region_id', 'status']);
            $table->dropIndex(['city_id', 'status']);
            $table->dropConstrainedForeignId('city_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropConstrainedForeignId('profile_address_id');
            $table->dropColumn('address_snapshot');
        });
    }
};
