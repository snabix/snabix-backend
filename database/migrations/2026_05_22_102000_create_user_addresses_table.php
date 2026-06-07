<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->uuid('id')
                ->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('region_id')
                ->constrained('regions')
                ->restrictOnDelete();
            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->restrictOnDelete();
            $table->string('label', 120)
                ->nullable();
            $table->string('address_line')
                ->nullable();
            $table->boolean('is_primary')
                ->default(false);
            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'region_id']);
            $table->index(['user_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
