<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table): void {
            $table->timestampTz('published_at')
                ->nullable();
            $table->timestampsTz();
            $table->uuid('id')
                ->primary();
            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->foreignId('author_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->unsignedTinyInteger('status')
                ->default(1)
                ->index();
            $table->string('title');
            $table->string('slug')
                ->unique();
            $table->string('category', 120);
            $table->string('eyebrow', 120)
                ->nullable();
            $table->string('description', 500);
            $table->string('thesis', 500)
                ->nullable();
            $table->string('reading_time', 40)
                ->nullable();
            $table->boolean('is_featured')
                ->default(false);
            $table->unsignedInteger('views_count')
                ->default(0);
            $table->json('seo')
                ->nullable();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
