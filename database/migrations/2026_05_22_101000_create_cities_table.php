<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->id();
            $table->foreignId('region_id')
                ->constrained('regions')
                ->cascadeOnDelete();
            $table->string('kladr_id')
                ->unique();
            $table->uuid('fias_guid')
                ->nullable()
                ->unique();
            $table->string('name');
            $table->string('name_alt')
                ->nullable();
            $table->string('slug');
            $table->string('label');
            $table->string('type')
                ->nullable();
            $table->string('type_short')
                ->nullable();
            $table->string('content_type')
                ->default('city');
            $table->string('okato')
                ->nullable()
                ->index();
            $table->string('oktmo')
                ->nullable()
                ->index();
            $table->unsignedInteger('zip')
                ->nullable()
                ->index();
            $table->unsignedInteger('population')
                ->nullable();
            $table->string('year_founded')
                ->nullable();
            $table->string('year_city_status')
                ->nullable();
            $table->string('name_en')
                ->nullable();
            $table->json('name_cases')
                ->nullable();
            $table->decimal('lat', 10, 7)
                ->nullable();
            $table->decimal('lon', 10, 7)
                ->nullable();
            $table->json('timezone')
                ->nullable();
            $table->boolean('is_capital')
                ->default(false)
                ->index();
            $table->boolean('is_dual_name')
                ->default(false)
                ->index();
            $table->boolean('is_active')
                ->default(true)
                ->index();
            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->unique(['region_id', 'label']);
            $table->index(['region_id', 'name']);
            $table->index(['is_active', 'sort_order']);
            $table->index(['lat', 'lon']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
