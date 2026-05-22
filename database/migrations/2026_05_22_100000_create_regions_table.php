<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->id();
            $table->string('kladr_id')
                ->unique();
            $table->uuid('fias_guid')
                ->nullable()
                ->unique();
            $table->string('name');
            $table->string('slug')
                ->unique();
            $table->string('label')
                ->unique();
            $table->string('type')
                ->nullable();
            $table->string('type_short')
                ->nullable();
            $table->string('content_type')
                ->default('region');
            $table->string('okato')
                ->nullable()
                ->index();
            $table->string('oktmo')
                ->nullable()
                ->index();
            $table->string('code', 8)
                ->nullable()
                ->unique();
            $table->string('iso_code', 16)
                ->nullable()
                ->unique();
            $table->unsignedInteger('population')
                ->nullable();
            $table->unsignedInteger('year_founded')
                ->nullable();
            $table->unsignedInteger('area')
                ->nullable();
            $table->string('fullname')
                ->nullable();
            $table->string('unofficial_name')
                ->nullable();
            $table->string('name_en')
                ->nullable();
            $table->string('district')
                ->nullable()
                ->index();
            $table->json('name_cases')
                ->nullable();
            $table->json('capital_data')
                ->nullable();
            $table->boolean('is_active')
                ->default(true)
                ->index();
            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->index(['is_active', 'sort_order']);
            $table->index(['district', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
