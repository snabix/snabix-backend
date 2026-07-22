<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('location_import_manifests', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->uuid('id')->primary();
            $table->string('source', 32)->default('russia');
            $table->string('source_version', 64)->index();
            $table->string('regions_file');
            $table->string('cities_file');
            $table->string('regions_checksum', 64);
            $table->string('cities_checksum', 64);
            $table->unsignedBigInteger('regions_size_bytes');
            $table->unsignedBigInteger('cities_size_bytes');
            $table->boolean('fresh')->default(false);
            $table->string('status', 24)->index();
            $table->json('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestampTz('started_at');
            $table->timestampTz('completed_at')->nullable();
        });

        Schema::create('location_import_staging', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('manifest_id')
                ->constrained('location_import_manifests')
                ->cascadeOnDelete();
            $table->string('kind', 16);
            $table->string('external_id');
            $table->string('parent_external_id')->nullable();
            $table->unsignedInteger('sort_order');
            $table->json('payload');

            $table->unique(
                ['manifest_id', 'kind', 'external_id'],
                'location_stage_manifest_kind_external_unique',
            );
            $table->index(
                ['manifest_id', 'kind', 'sort_order'],
                'location_stage_manifest_kind_sort_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_import_staging');
        Schema::dropIfExists('location_import_manifests');
    }
};
