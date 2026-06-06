<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->timestamps();
            $table->uuid('id')
                ->primary();
            $table->unsignedTinyInteger('catalog_type')->default(1)->index();
            $table->uuid('parent_id')
                ->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('path', 1024)->nullable()->index();
            $table->unsignedSmallInteger('depth')->default(0);

            $table->index(['parent_id', 'sort_order']);
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
