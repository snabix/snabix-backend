<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_post_blocks', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->uuid('id')
                ->primary();
            $table->foreignUuid('news_post_id')
                ->constrained('news_posts')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('type')
                ->index();
            $table->unsignedInteger('sort_order')
                ->default(1)
                ->index();
            $table->json('data');

            $table->index(['news_post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_post_blocks');
    }
};
