<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('external_source', 100)->nullable()->after('id');
            $table->string('external_id', 512)->nullable()->after('external_source');
            $table->timestamp('source_last_seen_at')->nullable()->after('external_id');

            $table->unique(
                ['external_source', 'external_id'],
                'categories_external_source_id_unique',
            );
            $table->index(
                ['external_source', 'is_active'],
                'categories_external_source_active_index',
            );
        });

        Schema::create('category_import_manifests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('source', 100);
            $table->string('source_version', 100);
            $table->string('source_url', 2048)->nullable();
            $table->char('checksum', 64);
            $table->string('status', 32)->index();
            $table->jsonb('records');
            $table->jsonb('diff');
            $table->jsonb('stats');
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'status']);
            $table->index(['source', 'checksum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_import_manifests');

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique('categories_external_source_id_unique');
            $table->dropIndex('categories_external_source_active_index');
            $table->dropColumn([
                'external_source',
                'external_id',
                'source_last_seen_at',
            ]);
        });
    }
};
